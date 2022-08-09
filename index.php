<?php
require __DIR__ . "/Img2Ascii.php";

$img2Ascii = new Img2Ascii();
$img2Ascii->setChars('@#%$?*+";:,. ');
//$img2Ascii->setChars('█▓▒░ ', true);

$outputStream = fopen("php://output", "a+");

switch ($_GET["s"] ?? 0)
{
    case 0:
        $img2Ascii->setBlockSize(1);
        $fontSize = [4, 2];
        break;

    case 1:
        $img2Ascii->setBlockSize(4);
        $fontSize = [10, 5];
        break;

    case 2:
        $img2Ascii->setBlockSize(8);
        $fontSize = [20, 10];
        break;

    case 3:
        $img2Ascii->setBlockSize(16);
        $fontSize = [40, 20];
        break;

    case 4:
        $img2Ascii->setBlockSize(32);
        $fontSize = [80, 40];
        break;

    default:
        $fontSize = [3, 2];
}

$imageFile = match ($_GET["img"] ?? null)
{
    "apple"     => "apple.jpg",
    "gradient"  => "gradient.png",
    "jerry"     => "jerry.jpg",
    "mickey"    => "mickey.jpg",
    "spongebob" => "spongebob.png",
    default     => $_GET["img"]
};
$img2Ascii->setImageFile(__DIR__ . "/" . $imageFile);


?>
<html style="margin:0;padding:0"></html>
<head></head>
<body style="margin:0;padding:0">
<pre style="font-size:<?php echo $fontSize[0]; ?>px;line-height:<?php echo $fontSize[1]; ?>px;">
<?php $img2Ascii->write(); ?>
</pre>
</body>
</html>
