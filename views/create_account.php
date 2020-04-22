<html>
    <head>
        <title>reCAPTCHA V2</title>
        <script type="text/javascript">
            var onloadCallback = function () {
                grecaptcha.render('html_element', {
                    'sitekey': 'your_site_key'
                });
            };
        </script>
    </head>
    <body>
        <form action="?" method="POST">
            <div id="html_element"></div>
            <div class="g-recaptcha" data-sitekey="your_secret_key"></div>
            <br>
            <input type="submit" value="Submit">
        </form>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                async defer>
        </script>
    </body>
</html>