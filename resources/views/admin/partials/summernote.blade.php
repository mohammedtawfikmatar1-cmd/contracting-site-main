<script src="{{ asset('public/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('public/admin/plugins/summernote/lang/summernote-ar-AR.js') }}"></script>
<script>
  /*
    ملاحظة احترافية للمستقبل:
    إدراج الصور داخل المحتوى بصيغة Base64 يبطئ الموقع ويضخم قاعدة البيانات.
    لذلك نعتمد هنا على رفع الصورة فور إدراجها إلى التخزين العام وإرجاع رابط (URL) فقط ليُحفظ داخل النص.
  */
  (function ($) {
    function csrfToken() {
      return $('meta[name="csrf-token"]').attr('content');
    }

    function uploadEditorImage(file, context, onSuccess, onError) {
      var formData = new FormData();
      formData.append('image', file);
      formData.append('context', context || 'general');

      $.ajax({
        url: "{{ route('admin.editor.upload') }}",
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        success: function (res) {
          if (res && res.url) return onSuccess(res.url);
          onError('استجابة غير متوقعة من السيرفر.');
        },
        error: function (xhr) {
          var msg = 'تعذر رفع الصورة.';
          try {
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
          } catch (e) {}
          onError(msg);
        }
      });
    }

    function initSummernote($el) {
      if (!$el.length || $el.data('summernote')) return;

      var context = $el.attr('data-editor-context') || 'general';

      $el.summernote({
        height: Number($el.attr('data-editor-height') || 260),
        lang: 'ar-AR',
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'table']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
          onImageUpload: function (files) {
            var $editor = $(this);
            if (!files || !files.length) return;

            Array.prototype.forEach.call(files, function (file) {
              uploadEditorImage(
                file,
                context,
                function (url) {
                  $editor.summernote('insertImage', url, function ($image) {
                    $image.attr('alt', 'image');
                  });
                },
                function (message) {
                  // إظهار رسالة واضحة للمستخدم داخل لوحة التحكم عند الفشل.
                  alert(message || 'تعذر رفع الصورة.');
                }
              );
            });
          }
        }
      });
    }

    $(function () {
      $('.js-editor').each(function () { initSummernote($(this)); });

      // دعم المحررات داخل تبويبات Bootstrap: تهيئة عند إظهار التبويب.
      $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $('.js-editor').each(function () { initSummernote($(this)); });
      });
    });
  })(jQuery);
</script>

