<script src="{{ asset('public/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('public/admin/plugins/summernote/lang/summernote-ar-AR.js') }}"></script>
<script>
  /*
    ملاحظة احترافية للمستقبل:
    إدراج الصور أو الفيديو داخل المحتوى بصيغة Base64 يبطئ الموقع ويضخم قاعدة البيانات.
    لذلك نعتمد هنا على رفع الوسائط فور إدراجها إلى التخزين العام وإرجاع رابط (URL) فقط ليُحفظ داخل النص.
  */
  (function ($) {
    var uploadedInThisPage = {};

    function csrfToken() {
      return $('meta[name="csrf-token"]').attr('content');
    }

    function makePreviewUrl(file) {
      try {
        if (window.URL && window.URL.createObjectURL) return window.URL.createObjectURL(file);
      } catch (e) {}
      return null;
    }

    function revokePreviewUrl(url) {
      try {
        if (url && window.URL && window.URL.revokeObjectURL) window.URL.revokeObjectURL(url);
      } catch (e) {}
    }

    function pendingUploadsInc($editor) {
      var $form = $editor.closest('form');
      var current = Number($form.data('editor-uploads-pending') || 0);
      $form.data('editor-uploads-pending', current + 1);
      return $form;
    }

    function pendingUploadsDec($form) {
      var current = Number($form.data('editor-uploads-pending') || 0);
      $form.data('editor-uploads-pending', Math.max(0, current - 1));
    }

    function maybeAutoSubmit($form) {
      // إذا حاول المستخدم الحفظ أثناء رفع الوسائط: نُكمل الرفع ثم نحفظ تلقائياً.
      if (!$form.data('editor-submit-waiting')) return;
      var pending = Number($form.data('editor-uploads-pending') || 0);
      if (pending > 0) return;

      $form.data('editor-submit-waiting', false);
      $form.find(':submit').prop('disabled', false);
      // إرسال النموذج بعد اكتمال رفع كل الوسائط (بدون تدخل المستخدم).
      $form.trigger('submit');
    }

    function uploadEditorMedia(file, context, type, onSuccess, onError) {
      var formData = new FormData();
      formData.append(type || 'image', file);
      formData.append('context', context || 'general');

      $.ajax({
        url: "{{ route('admin.editor.upload') }}",
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        success: function (res) {
          if (res && res.url) return onSuccess(res.url, res.path || null, res.mime || '');
          onError('استجابة غير متوقعة من السيرفر.');
        },
        error: function (xhr) {
          var msg = 'تعذر رفع الوسيط.';
          try {
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
          } catch (e) {}
          onError(msg);
        }
      });
    }

    function deleteEditorMedia(src, path) {
      if (!src && !path) return;

      $.ajax({
        url: "{{ route('admin.editor.image.destroy') }}",
        method: 'DELETE',
        data: {
          url: src || '',
          path: path || ''
        },
        headers: { 'X-CSRF-TOKEN': csrfToken() }
      });
    }

    function mediaPath($media) {
      return $media.attr('data-editor-upload-path') || $media.closest('[data-editor-upload-path]').attr('data-editor-upload-path') || '';
    }

    function insertUploadedVideo($editor, url, path, mime) {
      var safeUrl = $('<div>').text(url).html();
      var safePath = $('<div>').text(path || '').html();
      var safeMime = $('<div>').text(mime || 'video/mp4').html();
      var html = ''
        + '<p>'
        + '<video controls preload="metadata" style="max-width:100%;height:auto;" data-editor-upload-path="' + safePath + '">'
        + '<source src="' + safeUrl + '" type="' + safeMime + '" data-editor-upload-path="' + safePath + '">'
        + 'متصفحك لا يدعم تشغيل الفيديو.'
        + '</video>'
        + '</p>';

      $editor.summernote('pasteHTML', html);
    }

    function cleanupRemovedUploadsBeforeSubmit($form) {
      var currentHtml = '';

      $form.find('.js-editor').each(function () {
        var $editor = $(this);
        if ($editor.data('summernote')) {
          currentHtml += ' ' + $editor.summernote('code');
        } else {
          currentHtml += ' ' + ($editor.val() || '');
        }
      });

      Object.keys(uploadedInThisPage).forEach(function (path) {
        if (currentHtml.indexOf(path) === -1) {
          deleteEditorMedia('', path);
          delete uploadedInThisPage[path];
        }
      });
    }

    function initSummernote($el) {
      if (!$el.length || $el.data('summernote')) return;

      var context = $el.attr('data-editor-context') || 'general';
      var uploadVideoButton = function () {
        var ui = $.summernote.ui;

        return ui.button({
          contents: '<i class="note-icon-video"></i>',
          tooltip: 'رفع فيديو',
          click: function () {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = 'video/mp4,video/webm,video/ogg,video/quicktime';
            input.onchange = function () {
              var file = input.files && input.files[0] ? input.files[0] : null;
              if (!file) return;

              var $form = pendingUploadsInc($el);

              uploadEditorMedia(
                file,
                context,
                'video',
                function (url, path, mime) {
                  if (path) uploadedInThisPage[path] = true;
                  insertUploadedVideo($el, url, path, mime);
                  pendingUploadsDec($form);
                  maybeAutoSubmit($form);
                },
                function (message) {
                  if (window && window.console) {
                    console.error('Editor video upload failed:', message || 'تعذر رفع الفيديو.');
                  }
                  pendingUploadsDec($form);
                  maybeAutoSubmit($form);
                }
              );
            };
            input.click();
          }
        }).render();
      };

      $el.summernote({
        height: Number($el.attr('data-editor-height') || 260),
        lang: 'ar-AR',
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'videoUpload', 'table']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        buttons: {
          videoUpload: uploadVideoButton
        },
        callbacks: {
          onImageUpload: function (files) {
            var $editor = $(this);
            if (!files || !files.length) return;

            Array.prototype.forEach.call(files, function (file) {
              // تجربة "بدون أن يشعر المستخدم":
              // 1) نُدرج معاينة محلية مباشرة داخل المحرر (بدون انتظار الشبكة).
              // 2) نرفع الصورة في الخلفية.
              // 3) عند النجاح: نستبدل src بالـ URL النهائي (رابط تخزين).
              // بهذه الطريقة لا تُحفظ Base64 داخل قاعدة البيانات إطلاقاً.
              var previewUrl = makePreviewUrl(file);
              var insertedImageEl = null;

              if (previewUrl) {
                $editor.summernote('insertImage', previewUrl, function ($image) {
                  insertedImageEl = $image && $image.length ? $image.get(0) : null;
                  if (insertedImageEl) {
                    insertedImageEl.setAttribute('data-uploading', '1');
                    insertedImageEl.style.opacity = '0.6';
                    insertedImageEl.title = 'جارٍ رفع الصورة...';
                  }
                });
              }

              var $form = pendingUploadsInc($editor);

              uploadEditorMedia(
                file,
                context,
                'image',
                function (url, path) {
                  // إذا نجح الرفع: نستبدل الصورة المؤقتة بالرابط النهائي.
                  if (path) uploadedInThisPage[path] = true;

                  if (insertedImageEl && !$.contains(document, insertedImageEl)) {
                    deleteEditorMedia(url, path);
                    revokePreviewUrl(previewUrl);
                    pendingUploadsDec($form);
                    maybeAutoSubmit($form);
                    return;
                  }

                  if (insertedImageEl) {
                    insertedImageEl.src = url;
                    if (path) insertedImageEl.setAttribute('data-editor-upload-path', path);
                    insertedImageEl.removeAttribute('data-uploading');
                    insertedImageEl.style.opacity = '1';
                    insertedImageEl.title = '';
                    insertedImageEl.setAttribute('alt', 'image');
                  } else {
                    // fallback: في حال لم ندرج معاينة (مثلا متصفح لا يدعم ObjectURL)
                    $editor.summernote('insertImage', url, function ($image) {
                      $image.attr('alt', 'image');
                      if (path) $image.attr('data-editor-upload-path', path);
                    });
                  }
                  revokePreviewUrl(previewUrl);
                  pendingUploadsDec($form);
                  maybeAutoSubmit($form);
                },
                function (message) {
                  // بدون إزعاج المستخدم: نحذف المعاينة إذا فشل الرفع.
                  // (يمكن لاحقاً إضافة Toastr إذا رغبت، بدون تعديل تصميم اللوحة).
                  if (insertedImageEl && insertedImageEl.parentNode) {
                    insertedImageEl.parentNode.removeChild(insertedImageEl);
                  }
                  revokePreviewUrl(previewUrl);
                  if (window && window.console) {
                    console.error('Editor image upload failed:', message || 'تعذر رفع الصورة.');
                  }
                  pendingUploadsDec($form);
                  maybeAutoSubmit($form);
                }
              );
            });
          },
          onMediaDelete: function (target) {
            var $media = $(target);
            var path = mediaPath($media);

            if (path && uploadedInThisPage[path]) {
              deleteEditorMedia($media.attr('src') || $media.find('source').attr('src') || '', path);
              delete uploadedInThisPage[path];
            }
          }
        }
      });
    }

    $(function () {
      $('.js-editor').each(function () { initSummernote($(this)); });

      // منع حفظ نموذج فيه وسائط لم تكتمل (blob:) ثم الحفظ تلقائياً بعد اكتمال الرفع.
      $(document).on('submit', 'form', function (e) {
        var $form = $(this);
        var pending = Number($form.data('editor-uploads-pending') || 0);
        if (pending <= 0) {
          cleanupRemovedUploadsBeforeSubmit($form);
          return;
        }

        // إذا كان هذا submit ناتج عن maybeAutoSubmit (trigger submit) نسمح بالمرور
        if ($form.data('editor-submit-waiting')) {
          e.preventDefault();
          return;
        }

        e.preventDefault();
        $form.data('editor-submit-waiting', true);
        $form.find(':submit').prop('disabled', true);
      });

      // دعم المحررات داخل تبويبات Bootstrap: تهيئة عند إظهار التبويب.
      $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $('.js-editor').each(function () { initSummernote($(this)); });
      }) ;
    });
  })(jQuery);
</script>
