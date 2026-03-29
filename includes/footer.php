<script src="/assets/js/jquery-4.0.0.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // تابع بارگذاری صفحه بدون رفرش
    function loadAjaxContent(url) {
        $("#content-area").animate({opacity: 0.5}, 100);
        $.ajax({
            url: url,
            success: function(data) {
                // فقط بخش داخل #content-area را از فایل مقصد جدا کرده و جایگذاری می‌کند
                var filteredData = $(data).find('#content-area').length > 0 ? $(data).find('#content-area').html() : data;
                $("#content-area").html(filteredData).animate({opacity: 1}, 200);
                window.history.pushState(null, null, url);
            },
            error: function() {
                alert("خطا در بارگذاری صفحه!");
                $("#content-area").css("opacity", 1);
            }
        });
    }

    $(document).on('click', '.ajax-link', function(e) {
        e.preventDefault();
        var targetUrl = $(this).attr('data-url') || $(this).attr('href');
        if(targetUrl && targetUrl !== "#") {
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            loadAjaxContent(targetUrl);
        }
    });

    window.onpopstate = function() {
        loadAjaxContent(location.pathname);
    };
});
</script>
</body>
</html>