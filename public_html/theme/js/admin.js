function stop_waitMe(elm=null){
    if(elm==null) elm=$('body');
    else elm = $(elm);

    elm.waitMe('hide');
}

function run_waitMe(elm=null){

    if(elm==null) elm=$('body');
    else elm = $(elm);

    elm.waitMe({

        //none, rotateplane, stretch, orbit, roundBounce, win8,
        //win8_linear, ios, facebook, rotation, timer, pulse,
        //progressBar, bouncePulse or img
        effect: 'timer',

        //place text under the effect (string).
        text: 'Please wait...',

        //background for container (string).
        bg: 'rgba(255,255,255,0.7)',

        //color for background animation and text (string).
        color: '#000',

        //max size
        maxSize: '',

        //wait time im ms to close
        waitTime: -1,

        //url to image
        source: '',

        //or 'horizontal'
        textPos: 'vertical',

        //font size
        fontSize: '',

        // callback
        onClose: function() {}

    });
}
function waitMeMessage(message){
    $('.waitMe_text').html(message);
}
function isEmailAddress(mail)
{
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
    {
        return (true)
    }
    //alert("You have entered an invalid email address!")
    return (false)
}
function formatMoneyVND(money){
    // let config = { style: 'currency', currency: 'VND', maximumFractionDigits: 9}
    // return new Intl.NumberFormat('vi-VN', config).format(money);
    return formatMoney(money) +'đ';
}

/**
 * 1000000.01=>100,000
 * @param p_value
 * @returns {string}
 */
function formatMoney(p_value) {
    if (!p_value) return 0;
    if (typeof p_value == 'string')
        p_value=getRawMoney(p_value);

    // let value=Math.round(p_value);
    let value=formatNumber(p_value);
    // console.log(p_value);
    return value.toLocaleString();
}

/**
 * console.log(formatNumber("2.00"));  // 👉 2
 * console.log(formatNumber("0.50"));  // 👉 0.5
 * console.log(formatNumber("3.1415")); // 👉 3.1415
 * @param value
 * @returns {number}
 */
function formatNumber(value) {
    // if (typeof value !== 'string') return value;
    // Xoá khoảng trắng thừa
    // value = value.trim();
    // Ép sang số thực
    let num = parseFloat(value);

    // Nếu không phải số -> trả 0
    if (isNaN(num)) return 0;

    // Nếu là số nguyên (vd 2.00) thì bỏ phần .00
    if (Number.isInteger(num)) return num;
    // Là số thập phân (vd 0.50 → 0.5)
    return parseFloat(num.toFixed(2).toString());
}

function getRawMoney(p_value) {
    return intVal(String(p_value).replaceAll(',',''));
    // return String(p_value).replace(/\D/g, ""); // chỉ giữ lại chữ số
}
function intVal(i) {
    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
}
function subBarcode(number){
    try {
        return number.substr(-6);
    } catch (error) {
        return '';
    }
}
// const isMobile = window.innerWidth < 768;
const isMobile = window.innerWidth < 800;
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('.amount').on('keyup change', function() {
        let value=getRawMoney($(this).val());
        $(this).val(formatMoney(value));
    });
})