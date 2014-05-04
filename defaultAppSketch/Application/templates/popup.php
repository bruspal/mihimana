<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
      <title>%title%</title>
      <?php require 'headerHtml.php' ?>
    </head>
    <body class="popup" <?php if ( ! DEBUG ): ?>onblur="close()"<?php endif; ?>>
      <div id="content">
      <?php if (User::isAuthenticated()): ?>
        <?php echo User::renderFlashs() ?>
        <?php echo $sortieProgramme ?>
        <?php //include_partial('global/ajaxVisual') ?>
      <?php else: ?>
        <script type="text/javascript">
          $(document).ready(function(){
            window.opener.document.location.href='/';
            window.close();
          });
        </script>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        function hideFlashes()
        {
          $('#mainFlash').fadeOut('slow');
        }
        $(document).ready(function(){
          setTimeout("hideFlashes()",3000);
        });
      </script>
    </body>
</html>
