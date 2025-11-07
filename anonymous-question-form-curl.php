

<?php include('../templates/header.php'); 


?>

<br>
<?php
// choose visitor language, e.g. en | fi | fr | ru


define('SITE_ROOT', dirname(__DIR__));          // /home/users/.../raikkulenz.kapsi.fi
define('I18N_PATH', SITE_ROOT . '/language_json/');

$strings = json_decode(
    file_get_contents(I18N_PATH . 'languages_gpt.json'),
    true
);
function t1(string $id): string
{
    global $strings, $lang;
    return htmlspecialchars($strings[$lang][$id] ?? '', ENT_QUOTES, 'UTF-8');
}

?>
<br>

  <h2><?= t1('gpt5') ?></h2>
  <br>
  <p><?= t1('gpt6') ?></p>
  <br>
  <form enctype="multipart/form-data" action="chatgpt-response-handler-curl2.php" method="POST">


   <textarea name="prompt" rows="5" style="width: 80vw;"></textarea>

    <br><?= t1('gpt8') ?><br>
    <input name="filetto" type="file"><br>

    <button type="submit"><?= t1('gpt3') ?></button>
  </form>

<?php  include('../templates/footer.php'); ?>



</body>
</html>

