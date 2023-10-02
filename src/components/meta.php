<?php
// header("Location: https://eslite.amping.io/eslite/modernguru/index.php");
$filename = pathinfo(basename($_SERVER["REQUEST_URI"], '?' . $_SERVER["QUERY_STRING"]), PATHINFO_FILENAME);
if ($filename === 'dist' or $filename === 'modernguru') {
    echo '<script type="text/JavaScript">location.replace("./index.php");</script>';
};
?>
<meta charset="UTF-8">
<?php
$title = "AI靈感大師：澳洲3D光影觸動樂園 MODERN GURU AND THE PATH TO ARTIFICIAL HAPPINESS";
$description = "澳洲科技藝術團隊ENESS首度來台，帶來亞洲獨家的《AI靈感大師：澳洲3D光影觸動樂園》，透過AI技術、即時體感追蹤，創造沉浸式立體聲光體驗，尋求跨世代的連結與歡樂。";
$favicon = "./eslite.ico";
?>
<?php require_once("./components/fonts.php"); ?>
<title><?php echo $title; ?></title>
<meta name="darkreader-lock">
<meta name="description" content="<?php echo $title; ?>">
<meta property="og:title" content="<?php echo $description; ?>">
<meta property="og:description" content="<?php echo $description; ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="https://amping.io/eslite/modernguru/index.php">
<meta name="viewport" content="width = device-width, initial-scale = 1.0, minimum-scale = 1, maximum-scale = 1, user-scalable = no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta property="og:image" content="https://amping.io/eslite/modernguru/img/meta.png?0828">
<meta name="copyright" content="COPYRIGHT AMPING">
<meta http-equiv="pragma" content="no-cache">
<meta name="google" value="notranslate"><!-- 關閉 Google 自動翻譯 -->
<!-- CSS -->
<link rel="stylesheet" href="<?php echo "./css/webpack.css?r=" . rand(1, 999); ?>">
<!--<link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>-->
<!-- ico -->
<link rel="shortcut icon" href="<?php echo $favicon; ?>">
<script>
    var filename = "<?php echo $filename; ?>";
    // 隱藏所有 console.log()
    if (window.location.hostname === 'amping.io') console.log = () => {};
    // alert('本系統將於 01:00-04:00 進行系統升級與更新，敬請見諒。');
</script>
<style>
    body {
        background-image: url(./img/cover.png?20230816);
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
        background-size: cover;
        touch-action: manipulation;
    }
</style>