<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Empty App</title>
        <?php require 'headerHtml.php' ?>
        <style>
        </style>        
    </head>
    <body>
        <script type="text/javascript">
        </script>

        <div class="grid-container grid">
            <header class="grid-100 mobile-grid-100">
                <div class="grid-50 hide-on-mobile">
                    <div id="logo">Logo</div>
                    <div id="catchword"></div>
                </div>
                <div class="grid-50 mobile-grid-100"></div>
                <div class="grid-100 mobile-grid-100">
                </div>
            </header>
            <section class="grid-100 mobile-grid-100">
                <?php
                $flashes = mmUser::renderFlashs();
                if ($flashes != ''):
                    ?>
                    <div id="mainFlash" onclick="$('#mainFlash').hide()">
                        <div class="adminBox"></div>
                    <?php echo $flashes; ?>
                    </div>
                <?php endif; ?>
                <?php echo $sortieProgramme ?>
            </section>
            <footer class="grid-100 mobile-grid-100">
                <p>FOOTER</p>
            </footer>
        </div>
    </body>
</html>
