<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Purchase\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Service\ProductManager;
use Purchase\Service\PurchaseManager;
use Sulde\Service\Common\Define;
use Sulde\Service\SuldeFrontController;

class IndexController extends SuldeFrontController
{

    private $entityManager;
    private $purchaseManager;
    private $_openaiKey='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';

    public function __construct(EntityManager $entityManager, PurchaseManager $purchaseManager)
    {
        $this->entityManager = $entityManager;
        $this->purchaseManager = $purchaseManager;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        echo 'Voice!';
        return new ViewModel();
    }

    public function uploadAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return new JsonModel(['error' => 'Method not allowed']);
        }

        $files = $_FILES;
        if (empty($files['audio'])) {
            return new JsonModel(['error' => 'No audio file']);
        }

        $tmpPath = $files['audio']['tmp_name'];
        $filename = sys_get_temp_dir() . '/voice_' . uniqid() . '.wav';
        move_uploaded_file($tmpPath, $filename);

        // 1) Call Whisper (OpenAI) to transcribe
        $transcript = $this->transcribeAudio($filename);
        if (!$transcript) {
            return new JsonModel(['error' => 'STT failed']);
        }

        // 2) Call GPT to extract structured items
        $items = $this->GPTExtract($transcript);

        // 3) For each item, find product in DB
        $resolved = [];
        foreach ($items as $item) {
            $name = $item['product_name'] ?? '';
            $keyword = $this->extractKeywords($name,$this->_openaiKey);
            $item['keywords']=$keyword['keywords'];
            $found = $this->lookupProduct($item);
            $resolved[] = array_merge($item, ['db' => $found]);
        }

        // remove temp file
        @unlink($filename);
        return new JsonModel([
            'transcript' => $transcript,
            'items' => $resolved
        ]);
    }

    protected function transcribeAudio($filePath)
    {
        $url = "https://api.openai.com/v1/audio/transcriptions";
        $ch = curl_init();
        $cfile = curl_file_create($filePath, mime_content_type($filePath), basename($filePath));
        $data = [
            'file' => $cfile,
            'model' => 'whisper-1',
            'language' => 'vi',
            'prompt' => 'Giữ nguyên tên thương hiệu tiếng Anh như lifeboy, romano, xmen, lux, Clear, Omo, Dove, sunsilk ...'
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->_openaiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            error_log("Whisper error: $err");
            return null;
        }
        $j = json_decode($resp, true);
        return $j['text'] ?? null;
    }

    protected function GPTExtract($text)
    {
        $url = "https://api.openai.com/v1/chat/completions";
        $prompt = "Bạn là 1 parser. Từ văn bản sau (tiếng Việt), trích xuất danh sách sản phẩm dạng JSON " .
            "mỗi phần tử chứa: product_name, quantity (số), unit, price (số - VNĐ). " .
            "Nếu thiếu giá, để price = null. Nếu không rõ đơn vị, để unit = ''.\n\nVăn bản:\n" . $text .
            "\n\nTrả về CHÍNH XÁC JSON array.";
        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role"=>"system","content"=>"Bạn là một trợ lý phân tích hóa đơn."],
                ["role"=>"user","content"=>$prompt]
            ],
            "temperature" => 0
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->_openaiKey}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            error_log("GPT error: $err");
            return [];
        }
        $j = json_decode($resp, true);
        $content = $j['choices'][0]['message']['content'] ?? '[]';
        // Cẩn trọng: GPT trả văn bản, ta cố parse JSON
        $items = [];
        try {
            $items = json_decode($content, true);
            if (!is_array($items)) {
                // Nếu không parse được, cố gắng lấy phần JSON trong chuỗi
                preg_match('/(\[.*\])/s', $content, $m);
                if (!empty($m[1])) {
                    $items = json_decode($m[1], true);
                }
            }
        } catch (\Exception $e) {
            error_log("Parse GPT output failed: " . $e->getMessage());
            $items = [];
        }
        if (!is_array($items)) $items = [];
        return $items;
    }

    protected function lookupProduct($data)
    {
        $keywords=$data['keywords'];
//        $query=$data['product_name'] ?? '';
        $productManager = new ProductManager($this->entityManager);
        $products = $productManager->searchByKeywords($keywords, Define::ITEM_PAGE_COUNT, 0);

        /*$variantResult = array();
        foreach ($variants as $variantItem){
            $variant=$variantItem->serialize();
            $variant['product']=$variantItem->getProduct()->serialize();
            $variantResult[]=$variant;
        }
        if($variantResult)
            return ['found'=>true, 'product'=>$variantResult];*/

        $searchText = strtolower(implode(' ', $keywords));

        $scored = [];
        foreach ($products as $product) {
            $productKw = strtolower($product->getKeyword() ?? '');
            similar_text($searchText, $productKw, $percent);
            $lev = levenshtein($searchText, $productKw);
            $score = $percent - $lev / 5; // mix hai tiêu chí
            $scored[] = [
                'product' => $product,
                'score' => $score
            ];
        }

        // 3️⃣ Sắp xếp theo score giảm dần
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        // 4️⃣ Lấy top-N
        $matches = array_slice($scored, 0, Define::ITEM_PAGE_COUNT);
        if($matches) {
            $bestMatch=$matches[0]['product']?? null;
            return ['found'=>true,'product'=>$bestMatch?$bestMatch->serialize():null];
        }

        return ['found'=>false];
    }

    private function extractKeywords(string $text, string $apiKey): array
    {
        $prompt = "Phân tích câu sau và trích ra các từ khóa sản phẩm, "
            . "trả về danh sách từ khóa ngắn gọn (không dấu), dạng JSON, ví dụ: ['clear', 'dau goi', 'bac ha'].\n\n"
            . "Câu: \"$text\"";

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là trợ lý phân tích đơn hàng, trích keyword sản phẩm.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object']
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $apiKey"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        $content = $json['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true) ?: [];
    }
}