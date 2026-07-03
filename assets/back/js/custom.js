(function ($) {
    "use strict"; // Start of use strict

    function adminNotify(type, message, title) {
        if (!message) {
            return;
        }

        var notifyType = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger');
        var icon = notifyType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

        $.notify({
            icon: icon,
            title: title ? ' <strong>' + title + '</strong>' : '',
            message: message
        }, {
            element: 'body',
            position: null,
            type: notifyType,
            allow_dismiss: true,
            newest_on_top: true,
            showProgressbar: false,
            placement: {
                from: "top",
                align: "right"
            },
            offset: 20,
            spacing: 10,
            z_index: 1031,
            delay: 5000,
            timer: 1000,
            url_target: '_blank',
            mouse_over: null,
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            icon_type: 'class'
        });
    }

    window.AdminNotify = adminNotify;

    function confirmWithSweetAlert(options) {
        if (typeof Swal === 'undefined') {
            adminNotify('danger', options.text || options.title);
            return;
        }

        Swal.fire({
            title: options.title || 'Confirmar acao?',
            text: options.text || '',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Confirmar',
            cancelButtonText: options.cancelButtonText || 'Cancelar',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary mx-1',
                cancelButton: 'btn btn-secondary mx-1'
            }
        }).then(function (result) {
            if (result.isConfirmed && typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
        });
    }

    window.AdminConfirm = confirmWithSweetAlert;

    function closeAdminSidebar() {
        document.documentElement.classList.remove('nav_open');
        document.documentElement.classList.remove('admin-sidebar-overlay-open');
        document.querySelectorAll('.sidenav-toggler').forEach(function (button) {
            button.classList.remove('toggled');
        });
    }

    document.addEventListener('click', function (event) {
        var sidenavButton = event.target.closest('.sidenav-toggler');
        var topbarButton = event.target.closest('.topbar-toggler');

        if (!sidenavButton && !topbarButton) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if (sidenavButton) {
            document.documentElement.classList.toggle('nav_open');
            document.documentElement.classList.toggle('admin-sidebar-overlay-open', document.documentElement.classList.contains('nav_open'));
            sidenavButton.classList.toggle('toggled', document.documentElement.classList.contains('nav_open'));
        }

        if (topbarButton) {
            document.documentElement.classList.toggle('topbar_open');
            topbarButton.classList.toggle('toggled', document.documentElement.classList.contains('topbar_open'));
        }
    }, true);

    document.addEventListener('click', function (event) {
        if (event.target.closest('#admin-sidebar-backdrop')) {
            event.preventDefault();
            closeAdminSidebar();
        }
    });

    document.addEventListener('click', function (event) {
        var widget = document.getElementById('admin-support-widget');
        var toggle = document.getElementById('admin-support-toggle');
        var close = document.getElementById('admin-support-close');
        var panel = document.getElementById('admin-support-panel');

        if (!widget || !toggle || !panel) {
            return;
        }

        if (event.target.closest('#admin-support-toggle')) {
            event.preventDefault();
            var isOpen = widget.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            return;
        }

        if ((close && event.target.closest('#admin-support-close')) || !event.target.closest('#admin-support-widget')) {
            widget.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        var widget = document.getElementById('admin-support-widget');
        var toggle = document.getElementById('admin-support-toggle');
        var panel = document.getElementById('admin-support-panel');

        if (widget && toggle && panel) {
            widget.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');
        }

        closeAdminSidebar();
    });

    $(".sidebar-wrapper .sidebar-content ul.nav li.nav-item a").each(function () {
        var pageUrl = window.location.href.split(/[?#]/)[0];
        if (this.href == pageUrl) {
            var check = pageUrl.split("/");

            if (check.slice(-1)[0] != 'orders') {
                $(this).parent().addClass("active"); // add active to li of the current link
            }
            if ($(this).hasClass('sub-link')) {
                $(this).parent().parent().parent().parent().addClass('active');
                $(this).parent().parent().parent().prev().click(); // click the item to make it drop
            }
        }
    });

    $(document).on('click', '.sidebar .nav a:not([data-toggle="collapse"])', function () {
        if (window.matchMedia('(max-width: 991px)').matches) {
            closeAdminSidebar();
        }
    });

    $('#datepicker').datetimepicker({
        format: 'MM/DD/YYYY',
    });
    $('#datepicker1').datetimepicker({
        format: 'MM/DD/YYYY',
    });

    $('.timepicker').datetimepicker({
        format: 'h:mm A',
    });

    // IMAGE UPLOADING :)
    $(".upload-photo").on("change", function (e) {
        var path = $(this).parent().parent().prev().find('img');
        readURL(this, path);
    });

    function readURL(input, path) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                path.attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).on('click', '[data-target="#confirm-delete"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var href = $(this).data('href');
        var $modal = $('#confirm-delete');
        var title = $.trim($modal.find('.modal-title').first().text()) || 'Confirmar exclusao?';
        var text = $.trim($modal.find('.modal-body').first().text()) || 'Deseja realmente excluir este registro?';

        confirmWithSweetAlert({
            title: title,
            text: text,
            icon: 'warning',
            confirmButtonText: 'Excluir',
            cancelButtonText: 'Cancelar',
            onConfirm: function () {
                var $form = $modal.find('form.btn-ok').first();
                if ($form.length && href) {
                    $form.attr('action', href);
                    $form[0].submit();
                }
            }
        });
    });

    $(document).on('click', '[data-target="#statusModal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var href = $(this).data('href');
        var $modal = $('#statusModal');
        var title = $.trim($modal.find('.modal-title').first().text()) || 'Atualizar status?';
        var text = $.trim($modal.find('.modal-body').first().text()) || 'Deseja prosseguir?';

        confirmWithSweetAlert({
            title: title,
            text: text,
            icon: 'question',
            confirmButtonText: 'Atualizar',
            cancelButtonText: 'Cancelar',
            onConfirm: function () {
                if (href) {
                    window.location.href = href;
                }
            }
        });
    });

    $(document).on('submit', 'form[data-confirm-submit]', function (e) {
        var form = this;

        if ($(form).data('confirmed')) {
            return true;
        }

        e.preventDefault();

        confirmWithSweetAlert({
            title: $(form).data('confirm-title') || 'Confirmar acao?',
            text: $(form).data('confirm-text') || 'Deseja prosseguir?',
            icon: $(form).data('confirm-icon') || 'warning',
            confirmButtonText: $(form).data('confirm-button') || 'Confirmar',
            cancelButtonText: $(form).data('cancel-button') || 'Cancelar',
            onConfirm: function () {
                $(form).data('confirmed', true);
                form.submit();
            }
        });

        return false;
    });

    $('.radio-check').on('change', function () {
        if (this.checked) {
            $(this).parent().parent().next().removeClass('d-none');
        } else {
            $(this).parent().parent().next().addClass('d-none');
        }
    });

    //when submitted if there was an issue
    $("form.tab-form").on("submit", function () {
        let $this = $(this);
        let form_check = 1;

        $this.find('input,select').each(function () {
            if ($(this).prop('required')) {
                if ($(this).val() === "") {
                    form_check = 0;
                }
            }
        });

        if (form_check === 0) {

            $.notify({
                // options
                icon: 'flaticon-alarm-1',
                title: $this.data('title'),
                message: $this.data('error'),
            }, {
                // settings
                element: 'body',
                position: null,
                type: "danger",
                allow_dismiss: true,
                newest_on_top: false,
                showProgressbar: false,
                placement: {
                    from: "top",
                    align: "right"
                },
                offset: 20,
                spacing: 10,
                z_index: 1031,
                delay: 5000,
                timer: 1000,
                url_target: '_blank',
                mouse_over: null,
                animate: {
                    enter: 'animated fadeInDown',
                    exit: 'animated fadeOutUp'
                },
                onShow: null,
                onShown: null,
                onClose: null,
                onClosed: null,
                icon_type: 'class'
            });

            return false;
        }

    });

    $('.item-name').on('keyup', function () {

        let $this = $(this);

        let str = $this.val().replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '-').replace(/ /g, '-');

        $('#slug').val(str);

    });

    $('.admin-gallery').on('mouseover', function () {
        $(this).find('.remove-gallery-img').removeClass('d-none');
    });

    $('.admin-gallery').on('mouseout', function () {
        $(this).find('.remove-gallery-img').addClass('d-none');
    });

    $('#attr_name').on('keyup', function () {
        var text = $(this).val();
        var str = text.replace(/\ /g, "-");
        $('#attr_keyword').val(str.toLowerCase());
    });

    $('.addToMenu').on('click', function () {

        let $this = $(this);
        let title = $this.data('title');
        let keyword = title.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
        let dropdown = $this.data('dropdown');
        let href = $this.data('href');
        let target = $this.data('target');

        $('#section-list').append(`
        <div class="card mb-0 mt-2 mx-2 draggable-item">
            <div class="card-body">
                <div class="media">
                    <div class="media-body">
                        <h5 class="mb-1 mt-0">${title}</h5>
                        <input type="hidden" name="${keyword}[title]" value="${title}">
                        <input type="hidden" name="${keyword}[dropdown]" value="${dropdown}">
                        <input type="hidden" name="${keyword}[href]" value="${href}">
                        <input type="hidden" name="${keyword}[target]" value="${target}">
                    </div>
                    <i class="remove-menu fa fa-trash-alt"></i>
                </div>
            </div>
        </div>
    `);


    });

    $('#custom-submit').on('click', function () {
        let title = $('#title').val();
        if (title != '') {
            let keyword = title.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
            let dropdown = 'no';
            let href = $('#url').val();
            let target = $('#target').val();

            $('#section-list').append(`
            <div class="card mb-0 mt-2 mx-2 draggable-item">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="mb-1 mt-0">${title}</h5>
                            <input type="hidden" name="${keyword}[title]" value="${title}">
                            <input type="hidden" name="${keyword}[dropdown]" value="${dropdown}">
                            <input type="hidden" name="${keyword}[href]" value="${href}">
                            <input type="hidden" name="${keyword}[target]" value="${target}">
                        </div>
                        <i class="remove-menu fa fa-trash-alt"></i>
                    </div>
                </div>
            </div>
        `);
        }

    });


    $(document).on('click', '.remove-menu', function () {

        $(this).parent().parent().parent().remove();

    });



    $(function () {

        $('[data-target="#confirm-delete"], [data-target="#statusModal"]').removeAttr('data-toggle');
        $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip({
            container: 'body',
            trigger: 'hover focus'
        });

        $('.admin-notify-message').each(function () {
            adminNotify($(this).data('type'), $(this).data('message'), $(this).data('title'));
        });

        // editor
        if ($('.text-editor').length > 0) {

            $('.text-editor').summernote({
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen']],
                ],

                callbacks: {
                    onImageUpload: function (image) {
                        uploadImage(image[0]);
                    }
                }

            });


        }


        function uploadImage(image) {
            var data = new FormData();
            var url = summernot_upload_url;
            data.append("image", image);

            $.ajax({
                url: url,
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                method: "POST",
                success: function (data) {
                    $('.text-editor').summernote('insertImage', data.image);;
                },
                error: function (data) {
                    console.log(data);
                }
            });
        }

        // update progress bar

        function progressHandlingFunction(e) {
            if (e.lengthComputable) {
                $('progress').attr({ value: e.loaded, max: e.total });
                // reset progress on complete
                if (e.loaded == e.total) {
                    $('progress').attr('value', '0.0');
                }
            }
        }







        // Datatable
        if ($('#admin-table').length > 0) {

            $('#admin-table').DataTable({
                responsive: true,
                ordering: false
            });

        }


        // Set icon in edit
        if ($('#icon-value').length > 0) {
            $("input[name=icon]").val($('#icon-value').val());
        }

        // Tagify
        if ($('.tags').length > 0) {
            $('.tags').tagify();
        }

        // Magnific Popup
        if ($('.popup-link').length > 0) {
            $('.popup-link').magnificPopup({
                type: 'image'
            });
        }

        // Social Picker
        if ($('.social-picker').length > 0) {
            $('.social-picker').iconpicker();
        }

        // Sorting Section
        if ($('#section-list').length > 0) {
            var el = document.getElementById('section-list');
            Sortable.create(el, {
                animation: 100,
                group: 'list-1',
                draggable: '.draggable-item',
                handle: '.draggable-item',
                sort: true,
                filter: '.sortable-disabled',
                chosenClass: 'active'
            });
        }

        // Appending Social Icons To Items
        $('.add-social').on('click', function () {
            var text = $(this).data('text');
            $('#social-section').append(`
                <div class="d-flex">
                    <div>
                        <div class="form-group">
                            <button
                                class="btn btn-secondary social-picker"
                                name="social_icons[]"
                                data-icon="fab fa-font-awesome">
                            </button>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="form-group mb-1"><input type="text"
                                class="form-control"
                                name="social_links[]"
                                placeholder="${text}">
                        </div>
                    </div>
                    <div class="flex-btn">
                        <button type="button"
                            class="btn btn-danger remove-social">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
            `);

            $('.social-picker').iconpicker();

        });

        // Appending Specification To Items
        $('.add-specification').on('click', function () {
            var text = $(this).data('text');
            var text1 = $(this).data('text1');
            $('#specifications-section').append(`
            <div class="d-flex">
            <div class="flex-grow-1">
            <div class="form-group">
                <input type="text" class="form-control"
                    name="specification_name[]"
                    placeholder="${text}" value="">
                </div>
        </div>
        <div class="flex-grow-1">
            <div class="form-group">
                <input type="text" class="form-control"
                    name="specification_description[]"
                    placeholder="${text1}" value="">
                </div>
        </div>
        <div class="flex-btn">
                    <button type="button"
                        class="btn btn-danger remove-spcification">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
        </div>
            `);

            $('.social-picker').iconpicker();

        });


        // Appending License To Items
        $('.add-license').on('click', function () {
            var text = $(this).data('text');
            var text1 = $(this).data('text1');
            $('#license-section').append(`
            <div class="d-flex">
            <div class="flex-grow-1">
            <div class="form-group">
                <input type="text" class="form-control"
                    name="license_name[]"
                    placeholder="${text}" value="">
                </div>
        </div>
        <div class="flex-grow-1">
            <div class="form-group">
                <input type="text" class="form-control"
                    name="license_key[]"
                    placeholder="${text1}" value="">
                </div>
        </div>
        <div class="flex-btn">
                    <button type="button"
                        class="btn btn-danger remove-license">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
        </div>
            `);

            $('.social-picker').iconpicker();

        });

        $(document).on('click', '.remove-social', function () {
            if ($(this).parent().parent().parent().find('.social-picker').length > 1) {
                $(this).parent().parent().remove();
            }
        });

        $(document).on('click', '.remove-spcification', function () {
            $(this).parent().parent().remove();
        });
        $(document).on('click', '.remove-license', function () {
            $(this).parent().parent().remove();
        });


        $(document).on('change', '#category_id', function () {
            let category_id = $(this).val();
            let url = $(this).attr('data-href');
            getCategory(url, category_id);
        })

        $(document).on('change', '#subcategory_id', function () {
            let subategory_id = $(this).val();
            let url = $(this).attr('data-href');
            getSubCategory(url, subategory_id);
        })

        function getSubCategory(url, subcategory_id) {
            $.get(url + '?subcategory_id=' + subcategory_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#childcategory_id').html(start + view_html);
            })
        }

        function getCategory(url, category_id) {
            $.get(url + '?category_id=' + category_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#subcategory_id').html(start + view_html);
            })
        }




        // popular category script
        $(document).on('change', '#category_id1,#category_id2,#category_id3,#category_id4', function () {

            let category_id = $(this).val();
            let countNumber = '';
            let catId = $(this).attr('id');
            countNumber = catId.slice(countNumber.length - 1)
            let url = $(this).attr('data-href');
            MultigetCategory(url, category_id, countNumber);
        })

        $(document).on('change', '#subcategory_id1,#subcategory_id2,#subcategory_id3,#subcategory_id4', function () {
            let subategory_id = $(this).val();
            let countNumber = '';
            let catId = $(this).attr('id');
            countNumber = catId.slice(countNumber.length - 1)
            let url = $(this).attr('data-href');
            MultigetSubCategory(url, subategory_id, countNumber);
        })

        function MultigetSubCategory(url, subcategory_id, count) {
            $.get(url + '?subcategory_id=' + subcategory_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#childcategory_id' + count).html(start + view_html);
            })
        }

        function MultigetCategory(url, category_id, count) {
            $.get(url + '?category_id=' + category_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#subcategory_id' + count).html(start + view_html);
            })
        }

        // popular category script end

        // two column category script

        $(document).on('change', '#column_category_id1,#column_category_id2,#column_category_id3', function () {

            let category_id = $(this).val();
            let count = '';
            let catId = $(this).attr('id');
            count = catId.slice(count.length - 1);
            let url = $(this).attr('data-href');

            ColumngetCategory(url, category_id, count);
        })

        $(document).on('change', '#cloumn_subcategory_id1,#cloumn_subcategory_id2,#cloumn_subcategory_id3', function () {
            let subategory_id = $(this).val();
            let count = '';
            let catId = $(this).attr('id');
            count = catId.slice(count.length - 1);
            let url = $(this).attr('data-href');

            ColumngetSubCategory(url, subategory_id, count);
        })

        function ColumngetSubCategory(url, subcategory_id, count) {

            $.get(url + '?subcategory_id=' + subcategory_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#cloumn_childcategory_id' + count).html(start + view_html);
            })
        }

        function ColumngetCategory(url, category_id, count) {

            $.get(url + '?category_id=' + category_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                console.log('#column_subcategory_id' + count);
                $('#cloumn_subcategory_id' + count).html(start + view_html);
            })
        }

        // two column category script end


        // feature category script start
        $(document).on('change', '#feature_category_id1,#feature_category_id2,#feature_category_id3,#feature_category_id4', function () {

            let category_id = $(this).val();
            let count = '';
            let catId = $(this).attr('id');
            count = catId.slice(count.length - 1);
            let url = $(this).attr('data-href');

            FeaturegetCategory(url, category_id, count);
        })

        $(document).on('change', '#feature_subcategory_id1,#feature_subcategory_id2,#feature_subcategory_id3,#feature_subcategory_id4', function () {
            let subategory_id = $(this).val();
            let count = '';
            let catId = $(this).attr('id');
            count = catId.slice(count.length - 1);
            let url = $(this).attr('data-href');

            FeaturegetSubCategory(url, subategory_id, count);
        })

        function FeaturegetSubCategory(url, subcategory_id, count) {

            $.get(url + '?subcategory_id=' + subcategory_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                $('#feature_childcategory_id' + count).html(start + view_html);
            })
        }

        function FeaturegetCategory(url, category_id, count) {

            $.get(url + '?category_id=' + category_id, function (data) {
                let response = data.data;
                let view_html = ``;
                $.each(response, function (key, value) {
                    view_html += `<option value="${value.id}">${value.name}</option>`;
                });
                let start = `<option value="">Select One</option>`;
                console.log('#column_subcategory_id' + count);
                $('#feature_subcategory_id' + count).html(start + view_html);
            })
        }


        // feature category script end

    });


    // flash deal item select date

    $(document).on('change', '#is_type', function () {
        if ($(this).val() == 'flash_deal') {
            $('.show-datepicker').removeClass('d-none');
            $('input .datepicker').prop('required', true);
        } else {
            $('.show-datepicker').addClass('d-none');
            $('input .datepicker').val('');
            $('input .datepicker').prop('required', false);
        }
    })


    // product sorting js




    // tickets sorting js
    $(document).on('change', '.tickets_sort', function () {
        let value = $(this).val();
        window.location.href = $('#tickets_url').val() + "?type=" + value;
    })


    // add more language field script

    let appendHtml = `
        <tr>
            <td>
                <input type="text" class="form-control" name="keys[]" value="">
            </td>
            <td><input type="text" class="form-control" name="values[]" value=""></td>
            <td>
                <div class="delete_language_field">
                <button class="btn btn-danger btn-sm"> <i class="fas fa-trash "></i></button>
            </div></td>
        </tr>`;
    $(document).on('click', '#add_more_language', function () {
        $('.new-field').append(appendHtml);
    });

    $(document).on('click', '.delete_language_field', function () {
        $(this).parent().parent().remove();
    });


    // Notification

    function updateNotificationCount(count) {
        var total = parseInt(count, 10) || 0;
        $('#notification-count').text(total);

        if (total === 0) {
            $('#notification-count').addClass('d-none');
        } else {
            $('#notification-count').removeClass('d-none');
        }
    }

    $('#alertsDropdown').on('click', function () {
        $('#display-notf').load($('#display-notf').data('href'));
    });

    $(document).on('click', '.notification-read-link', function (e) {
        e.preventDefault();

        var $this = $(this);
        var url = $this.data('href') || $this.attr('href');

        $.get(url, function (response) {
            if (response && typeof response.count !== 'undefined') {
                updateNotificationCount(response.count);
            }

            window.location.href = response && response.redirect ? response.redirect : $this.attr('href');
        }).fail(function () {
            window.location.href = $this.attr('href');
        });
    });

    $(document).on('click', '.clear-notf', function (e) {
        e.preventDefault();

        var url = $(this).data('href');
        $.get(url, function (response) {
            updateNotificationCount(response && typeof response.count !== 'undefined' ? response.count : 0);

            if (response && response.html) {
                $('#display-notf').html(response.html);
            } else if ($('#display-notf').length) {
                $('#display-notf').load($('#display-notf').data('href'));
            }

            $('.notification-list-item').remove();
        });
    });

    updateNotificationCount($('#notification-count').text());

    // Admin theme
    function setAdminThemeIcon() {
        var isDark = document.documentElement.classList.contains('admin-theme-dark');
        $('#admin-theme-toggle i').attr('class', isDark ? 'fas fa-sun' : 'fas fa-moon');
    }

    setAdminThemeIcon();

    $(document).on('click', '#admin-theme-toggle', function () {
        document.documentElement.classList.toggle('admin-theme-dark');
        localStorage.setItem('admin-theme', document.documentElement.classList.contains('admin-theme-dark') ? 'dark' : 'light');
        setAdminThemeIcon();
    });


    // bulk delete start 

    $(document).on('change', '.bulk_all_delete', function () {
        let target = $(this).attr('data-target');
        if ($(this).is(':checked')) {
            $('#' + target + ' .bulk-item').prop('checked', true);
        } else {
            $('#' + target + ' .bulk-item').prop('checked', false);
        }

        bulk_select(target);
    });


    $(document).on('change', '#product-bulk-delete input.bulk-item', function () {
        bulk_select('product-bulk-delete');
    })

    $(document).on('change', '#transaction-bulk-delete input.bulk-item', function () {
        bulk_select('transaction-bulk-delete');
    })
    $(document).on('change', '#order-bulk-delete input.bulk-item', function () {
        bulk_select('order-bulk-delete');
    })
    $(document).on('change', '#blog-bulk-delete input.bulk-item', function () {
        bulk_select('blog-bulk-delete');
    })


    function bulk_select(target) {
        var selected = [];
        $('#' + target + ' input:checked').each(function () {
            selected.push($(this).val());
        });
        $('#bulk_delete').val(selected);

    }

    // multiple home page slider js start 

    $(document).on('change', '#home_page_select', function () {
        let home_page = $(this).val();
        let label1 = 'Logo';
        let message1 = 'Dimensão ideal: 260 x 80 px';
        let message_1 = 'Dimensão ideal: 1920 x 750 px';
        let slider_image_text1 = 'Set Slider Image';

        let label2 = 'Feature Image';
        let message2 = 'Dimensão ideal: 870 x 530 px';
        let message_2 = 'Dimensão ideal: 1920 x 750 px';
        let slider_image_text2 = 'Set Background Image';

        if (home_page == 'theme3' || home_page == 'theme4') {
            $('#change_label').text(label2);
            $('#change_message').text(message2);
            $('#chenge_label2').text(message_2);
            $('#slider_text').text(slider_image_text2);
        } else {
            $('#change_label').text(label1);
            $('#change_message').text(message1);
            $('#chenge_label2').text(message_1);
            $('#slider_text').text(slider_image_text1);
        }
    })

    // multiple home page slider js end

    // attribute options stock js start
    $(document).on('click', '#unlimited', function () {
        if ($(this).is(':checked')) {
            $('#stock').val('unlimited');
        } else {
            $('#stock').val('');
        }
    })

    $(document).on('click', '.save__edit', function () {
        $('.check_button').val('1');
    })


    $(document).on('change', '#gallery_file', function () {


        for (let i = 0; i < this.files.length; ++i) {
            let filereader = new FileReader();

            filereader.onload = function () {

                let xxx = `
                    <div class="single-g-item d-inline-block m-2">
                            <span 
                             class="remove-gallery-img">
                                <i class="fas fa-trash reader_file_remove"></i>
                            </span>
                            <a class="popup-link" href="${this.result}">
                                <img class="admin-gallery-img" src="${this.result}"
                                    alt="No Image Found">
                            </a>
                    </div>
                
            `;
                $(".gallery_image_view").append(xxx);
            };
            filereader.readAsDataURL(this.files[i]);
        }


    })


    $(document).on('click', '.reader_file_remove', function () {
        $(this).parent().parent().remove();
    })


    // Upload padrao: clique, arrasta e solta, compactacao e preview imediato
    var adminUploadMaxBytes = 1800 * 1024;
    var adminUploadMaxDimension = 1600;
    var adminUploadDefaultDimension = '1200 x 900 px';
    var adminUploadDimensionRules = [
        { path: '/settings/system', name: 'logo', size: '260 x 80 px' },
        { path: '/settings/system', name: 'favicon', size: '512 x 512 px' },
        { path: '/settings/system', name: 'loader', size: '200 x 200 px' },
        { path: '/settings/system', name: 'meta_image', size: '1200 x 630 px' },
        { path: '/settings/system', name: 'footer_gateway_img', size: '600 x 120 px' },
        { path: '/settings/announcement', name: 'announcement', size: '1200 x 360 px' },
        { path: '/settings/maintainance', name: 'maintainance_image', size: '900 x 600 px' },
        { path: '/platform/whatsapp', name: 'site_whatsapp_attendant_photo', size: '400 x 400 px' },
        { path: '/platform/pwa', name: 'pwa_icon', size: '512 x 512 px' },
        { path: '/platform/pwa', name: 'pwa_icon_192', size: '192 x 192 px' },
        { path: '/platform/pwa', name: 'pwa_icon_512', size: '512 x 512 px' },
        { path: '/platform/pwa', name: 'pwa_install_popup_image', size: '640 x 640 px' },
        { path: '/platform/popups', name: 'promo_popup_image', size: '900 x 900 px' },
        { path: '/banner', name: 'image', size: '1200 x 500 px' },
        { path: '/slider', name: 'logo', size: '260 x 80 px' },
        { path: '/slider', name: 'photo', size: '1920 x 750 px' },
        { path: '/home-page', name: 'img1', size: '870 x 530 px' },
        { path: '/home-page', name: 'img2', size: '870 x 530 px' },
        { path: '/home-page', name: 'img3', size: '870 x 530 px' },
        { path: '/home-page', name: 'img4', size: '870 x 530 px' },
        { path: '/home-page', name: 'img5', size: '870 x 530 px' },
        { path: '/category', name: 'photo', size: '600 x 600 px' },
        { path: '/subcategory', name: 'photo', size: '600 x 600 px' },
        { path: '/childcategory', name: 'photo', size: '600 x 600 px' },
        { path: '/brand', name: 'photo', size: '360 x 180 px' },
        { path: '/service', name: 'photo', size: '180 x 180 px' },
        { path: '/feature', name: 'feature_image', size: '1200 x 630 px' },
        { path: '/post', name: 'photo[]', size: '1200 x 630 px' },
        { path: '/item', name: 'photo', size: '900 x 1200 px' },
        { path: '/item', name: 'galleries[]', size: '900 x 1200 px' },
        { path: '/staff', name: 'photo', size: '400 x 400 px' },
        { path: '/dashboard/profile', name: 'photo', size: '400 x 400 px' },
        { path: '/settings/payment', name: 'photo', size: '420 x 220 px' }
    ];

    function getUploadInputName(input) {
        return ($(input).attr('name') || '').toString();
    }

    function isAdminImageUpload(input) {
        var $input = $(input);
        var accept = ($input.attr('accept') || '').toLowerCase();

        return $input.hasClass('upload-photo') || accept.indexOf('image') !== -1;
    }

    function getUploadIdealDimension(input) {
        var pathname = window.location.pathname.toLowerCase();
        var inputName = getUploadInputName(input);
        var i;

        for (i = 0; i < adminUploadDimensionRules.length; i++) {
            if (pathname.indexOf(adminUploadDimensionRules[i].path) !== -1 && inputName === adminUploadDimensionRules[i].name) {
                return adminUploadDimensionRules[i].size;
            }
        }

        if (inputName.indexOf('icon') !== -1 || inputName.indexOf('favicon') !== -1) {
            return '512 x 512 px';
        }

        if (inputName.indexOf('logo') !== -1) {
            return '260 x 80 px';
        }

        if (inputName.indexOf('banner') !== -1 || inputName.indexOf('slider') !== -1) {
            return '1920 x 750 px';
        }

        if (inputName.indexOf('avatar') !== -1 || inputName.indexOf('attendant') !== -1 || inputName.indexOf('photo') !== -1) {
            return adminUploadDefaultDimension;
        }

        return adminUploadDefaultDimension;
    }

    function getUploadHint(input, loading) {
        var dimension = getUploadIdealDimension(input);
        var action = loading ? 'Otimizando imagem antes do envio...' : 'Clique ou arraste a imagem aqui';

        if (!dimension) {
            return action;
        }

        return action + '<span class="admin-upload-dropzone__dimension">Dimensão ideal: ' + dimension + '</span>';
    }

    function renderInlineUploadDimension(input) {
        var dimension = getUploadIdealDimension(input);
        var $input = $(input);
        var $hint = $input.next('.admin-upload-inline-dimension');

        if (!dimension) {
            return;
        }

        if (!$hint.length) {
            $hint = $('<small class="admin-upload-inline-dimension"></small>');
            $input.after($hint);
        }

        $hint.text('Dimensão ideal: ' + dimension);
    }

    function setUploadLabel(input, text, loading) {
        var $dropzone = $(input).closest('.file');
        var $label = $dropzone.find('.file-custom').first();

        if (!$label.length) {
            return;
        }

        $label.html(
            '<span class="admin-upload-dropzone__icon"><i class="fas fa-cloud-upload-alt"></i></span>' +
            '<span class="admin-upload-dropzone__title">' + text + '</span>' +
            '<small class="admin-upload-dropzone__hint">' + getUploadHint(input, loading) + '</small>'
        );
    }

    function refreshUploadPreview(input) {
        var file = input.files && input.files[0] ? input.files[0] : null;
        var $input = $(input);
        var $dropzone = $input.closest('.file');

        if (!file) {
            return;
        }

        setUploadLabel(input, file.name, false);

        if (!file.type || file.type.indexOf('image/') !== 0) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            var $preview = $dropzone.prevAll('img.admin-img:first');

            if (!$preview.length) {
                $preview = $dropzone.closest('.form-group, .card-body, .col-lg-6, .col-md-6').find('img.admin-img:first');
            }

            if ($preview.length) {
                $preview.attr('src', event.target.result);
            }
        };
        reader.readAsDataURL(file);
    }

    function replaceInputFile(input, file) {
        if (!window.DataTransfer) {
            return false;
        }

        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        return true;
    }

    function compressUploadImage(input, callback) {
        var file = input.files && input.files[0] ? input.files[0] : null;

        if (input.multiple || !file || !file.type || file.type.indexOf('image/') !== 0 || file.type === 'image/gif' || file.size <= adminUploadMaxBytes) {
            callback();
            return;
        }

        var image = new Image();
        var objectUrl = URL.createObjectURL(file);
        setUploadLabel(input, file.name, true);

        image.onload = function () {
            var width = image.width;
            var height = image.height;
            var ratio = Math.min(1, adminUploadMaxDimension / Math.max(width, height));
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');

            canvas.width = Math.max(1, Math.round(width * ratio));
            canvas.height = Math.max(1, Math.round(height * ratio));
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(objectUrl);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    callback();
                    return;
                }

                var baseName = file.name.replace(/\.[^.]+$/, '');
                var compressedFile = new File([blob], baseName + '.jpg', {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });

                if (compressedFile.size < file.size && replaceInputFile(input, compressedFile)) {
                    setUploadLabel(input, compressedFile.name, false);
                }

                callback();
            }, 'image/jpeg', .82);
        };

        image.onerror = function () {
            URL.revokeObjectURL(objectUrl);
            callback();
        };

        image.src = objectUrl;
    }

    function prepareUploadDropzones(context) {
        $(context || document).find('input[type="file"]').each(function () {
            if (!isAdminImageUpload(this)) {
                return;
            }

            var $input = $(this);
            var $dropzone = $input.closest('.file');

            if (!$dropzone.length) {
                renderInlineUploadDimension(this);
                return;
            }

            if ($dropzone.data('dropzoneReady')) {
                return;
            }

            $dropzone
                .addClass('admin-upload-dropzone')
                .attr('data-upload-dropzone', 'true')
                .data('dropzoneReady', true);

            var $custom = $dropzone.find('.file-custom').first();
            if ($custom.length && !$custom.find('.admin-upload-dropzone__hint').length) {
                $custom.html(
                    '<span class="admin-upload-dropzone__icon"><i class="fas fa-cloud-upload-alt"></i></span>' +
                    '<span class="admin-upload-dropzone__title">' + ($custom.text().trim() || 'Selecionar arquivo') + '</span>' +
                    '<small class="admin-upload-dropzone__hint">' + getUploadHint(this, false) + '</small>'
                );
            }
        });
    }

    prepareUploadDropzones(document);

    $(document).on('change', 'input[type="file"]', function () {
        if (!isAdminImageUpload(this)) {
            return;
        }

        var input = this;
        compressUploadImage(input, function () {
            refreshUploadPreview(input);
        });
    });

    $(document).on('dragenter dragover', '.admin-upload-dropzone', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).addClass('is-dragover');
    });

    $(document).on('dragleave dragend drop', '.admin-upload-dropzone', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).removeClass('is-dragover');
    });

    $(document).on('drop', '.admin-upload-dropzone', function (event) {
        var files = event.originalEvent && event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : null;
        var input = $(this).find('input[type="file"]').filter(function () {
            return isAdminImageUpload(this);
        }).get(0);

        if (!input || !files || !files.length) {
            return;
        }

        input.files = files;
        $(input).trigger('change');
    });





})(jQuery); // End of use strict
