<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
      <title>%title%</title>
      <?php if(DEBUG): ?>
      <meta http-equiv="cache-control" content="max-age=0" />
      <meta http-equiv="cache-control" content="no-cache" />
      <meta http-equiv="expires" content="0" />
      <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
      <meta http-equiv="pragma" content="no-cache" />      
      <?php endif; ?>
      <?php require 'headerHtml.html' ?>
    </head>
    <body>
      <?php if(DEBUG): ?>
      <div style="position: fixed; width: 200px; height: 300px; top: 0px; left: 0px; background-color: yellow; border: 2px solid red; z-index: -1">
        <div><strong>Debug window</strong></div>
        <div id="debugZone"></div>
      </div>
      <?php endif; ?>
      <script type="text/javascript">
        $(document).ready(function(){
          $(document.body).keydown(function(event){
            $('#debugZone').html('which : '+event.which+'<br />keyCode : '+event.keyCode);
            if (event.which == 112) {
              mdPopup("Ici viendra l'aide en ligne :D", "Aide");
              event.preventDefault();
            }
            if (event.which == 116) {
              mdPopup("Refresh par F5 interdit", "&gt;&lt; !!");
              event.preventDefault();
            }
            if (event.which == 13) {
              //event.preventDefault();
            }
          });
        });
      </script>
      <div id="mainLayout">
        <div id="header">
          <span id="horloge"></span>
          <a href="?"><img src="images/logo_def.png" alt="Logo" id="mainLogo" /></a>
          <span id="mainToolsBar"><?php if (mmUser::isAuthenticated() && MODE_INSTALL === false) echo new mmMenu('principal') ?></span>
        </div>
        <div id="content">
            
          <?php
          $flashes = User::renderFlashs();
          if ($flashes != ''): ?>
          <div id="mainFlash" onclick="$('#mainFlash').hide()">
            <div class="adminBox">Fermer</div>
            <?php echo $flashes; ?>
          </div>
          <?php endif; ?>
          <?php echo $sortieProgramme ?>
        </div>
        <div id="footer">
          &copy; Bruspal 2013
        </div>
      </div>
      <?php if (! DEBUG): ?>
      <script type="text/javascript">
        function hideFlashes()
        {
          $('#mainFlash').fadeOut('slow');
        }
        $(document).ready(function(){
          setTimeout("hideFlashes()",6000);
        });
      </script>
      <?php endif; ?>
    </body>
</html>
