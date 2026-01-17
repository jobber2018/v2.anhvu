/**
 * Hàm khởi tạo Dropzone tùy chỉnh
 * @param {string} selector - CSS selector của Dropzone container
 * @param {string} uploadUrl - URL upload file
 * @param {string} deleteUrl - URL xoá file
 * @param fileListUrl - URL get file tren server
 * @param defaultMessage
 */
function initCustomDropzone({selector, uploadUrl, deleteUrl,fileListUrl, defaultMessage,acceptedFiles="image/*,application/pdf"}) {
    Dropzone.autoDiscover = false;
    // const dzElement = document.getElementById(selector);
    const dzElement = document.querySelector(".dropzone");

    // --- Tạo wrapper nếu chưa có ---
    let wrapper = dzElement.closest(".dropzone-wrapper");
    if (!wrapper) {
        wrapper = document.createElement("div");
        wrapper.classList.add("dropzone-wrapper");
        wrapper.style.position = "relative";

        dzElement.parentNode.insertBefore(wrapper, dzElement);
        wrapper.appendChild(dzElement);
    }

    // --- Tạo overlay loading ---
    const loadingOverlay = document.createElement("div");
    loadingOverlay.id = "dz-loading";
    loadingOverlay.className = "dropzone-loading-overlay";
    loadingOverlay.style.display = "none";
    loadingOverlay.innerHTML = `<i class="fas fa-spinner fa-spin"></i>&nbsp; Đang tải file...`;
    wrapper.appendChild(loadingOverlay);

    // --- Hàm show/hide loading ---
    function showDropzoneLoading() {
        wrapper.classList.add("dropzone-disabled");
        loadingOverlay.style.display = "flex";
    }

    function hideDropzoneLoading() {
        wrapper.classList.remove("dropzone-disabled");
        loadingOverlay.style.display = "none";
    }

    function applyHighlightIfDefault(file) {
        let oldLabel = file.previewElement.querySelector('.dz-default-label');
        if(file?.default==1){
            if (!oldLabel) {
                let label = document.createElement("div");
                label.className = "dz-default-label";
                label.innerText = "Mặc định";
                file.previewElement.appendChild(label);
                file.previewElement.classList.add("dz-highlight");
            }
        }else{
            if (oldLabel) {
                oldLabel.remove();
            }
            file.classList?.remove('dz-highlight');
        }
    }

    // Hàm helper kiểm tra file hình ảnh (bao gồm webp)
    function isImageFile(file) {
        // console.log('is image');
        const imageExtensions = ['jpg','jpeg','png','gif','webp'];
        if(file.type && file.type.startsWith("image/")) {
            return true;
        } else {
            // fallback: kiểm tra extension
            // console.log('file.url:',file.type);
            const ext = file.url.split('.').pop().toLowerCase();
            return imageExtensions.includes(ext);
        }
    }
    // Hàm thêm nút xoá dùng chung
    function addDeleteButton(file, dzInstance, deleteUrl) {
        if(file.previewElement.querySelector('.dz-delete-btn')) return; // tránh lặp

        const btn = Dropzone.createElement('<div class="dz-delete-btn"><i class="fas fa-trash"></i></div>');
        file.previewElement.appendChild(btn);

        btn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();

            Swal.fire({
                title: "Xoá dữ liệu?",
                text: "Xoá file sẽ không khôi phục lại được, bạn chắc vẫn muốn xoá?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Xoá file",
                cancelButtonText:"Huỷ xoá"
            }).then((result) => {
                if(result.isConfirmed && file.id) {
                    $.ajax({
                        method: "POST",
                        headers: {Accept: "application/json; charset=utf-8"},
                        url: deleteUrl,
                        data: {id:file.id}
                    }).done(function (response) {
                        if(response.status==1) {
                            dzInstance.removeFile(file);
                            toggleMessage();
                            //applyHighlightIfDefault(file);
                            Toast.fire({title: response.message, icon: 'success'});
                        } else {
                            Toast.fire({title: response.message, icon: 'error'});
                        }
                    });
                }
            });
        });
    }

    return new Dropzone(selector, {
        url: uploadUrl,
        addRemoveLinks: false,
        acceptedFiles: acceptedFiles,
        dictDefaultMessage: defaultMessage ?? 'Kéo thả ảnh, chứng từ vào đây hoặc click để chọn...',
        init: function () {
            const dz = this;
            function toggleMessage() {
                $(".dz-message").toggle(dz.files.length === 0);
            }
            //load file
            if(fileListUrl!='#') {
                showDropzoneLoading();
                // 1️⃣ Load file từ server
                fetch(fileListUrl)  // API trả về list file: [{name, url, type}]
                    .then(res => res.json())
                    .then(files => {
                        files.forEach(fileInfo => {
                            let mockFile = {
                                url: fileInfo.url,
                                size: fileInfo.size,
                                id: fileInfo.id,
                                type: fileInfo.type,
                                default: (fileInfo?.default ?? 0),
                                accepted: true // để Dropzone hiểu đây là file hợp lệ
                            };
                            dz.emit("addedfile", mockFile);
                            // console.log(mockFile.type);
                            if (mockFile.type === "application/pdf") {
                                dz.emit("thumbnail", mockFile, "/assets/images/icons/pdf-icon.png");
                                mockFile.previewElement.classList.add("pdf-preview");

                                mockFile.previewElement.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (mockFile.url) window.open(mockFile.url, "_blank");
                                });
                            } else if (isImageFile(mockFile)) {
                                dz.emit("thumbnail", mockFile, mockFile.url);
                                // Bọc ảnh trong link Lightbox
                                let img = $(mockFile.previewElement).find("img[data-dz-thumbnail]");
                                if (!img.parent().is('a')) {
                                    img.wrap(`<a href="${mockFile.url}" data-lightbox="dropzone"></a>`);
                                }
                            }

                            dz.emit("complete", mockFile);
                            dz.files.push(mockFile);
                            addDeleteButton(mockFile, dz, deleteUrl);
                        });
                        hideDropzoneLoading();
                        toggleMessage();
                    });
            }else{toggleMessage();}
            //end load file

            dz.on("addedfile", function (file) {
                addDeleteButton(file,dz,deleteUrl);
                applyHighlightIfDefault(file);
                toggleMessage();
            });

            // Xử lý upload thành công
            dz.on("success", function(file, response) {
                if(response.status == 1) {
                    file.previewElement.classList.add("dz-success");
                    file.url = response.url;
                    file.id = response.id;
                    file.default=(response?.default ?? 0);
                } else {
                    file.previewElement.classList.add("dz-error");
                    Toast.fire({title: response.message, icon: 'error'});
                }
                toggleMessage();
            });

            // Xử lý hoàn tất hiển thị thumbnail và Lightbox
            dz.on("complete", function(file){
                if(file.type === "application/pdf") {
                    dz.emit("thumbnail", file, "/assets/images/icons/pdf-icon.png");
                    file.previewElement.classList.add("pdf-preview");

                    file.previewElement.addEventListener("click", (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if(file.url) window.open(file.url, "_blank");
                    });
                } else if(isImageFile(file)) {
                    dz.emit("thumbnail", file, file.url);
                    // Bọc ảnh trong link Lightbox
                    let img = $(file.previewElement).find("img[data-dz-thumbnail]");
                    if(!img.parent().is('a')) {
                        img.wrap(`<a href="${file.url}" data-lightbox="dropzone"></a>`);
                    }
                }
                applyHighlightIfDefault(file);
                toggleMessage();
            });
        }
    });

    // return myDropzone;
}