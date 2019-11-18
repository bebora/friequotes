
<?php

require "../common.php";
define ('SITE_ROOT', realpath(getcwd()));
check_token(LoginLevel::USER);

// Code from https://pqina.nl/blog/creating-thumbnails-with-php/
// Link image type to correct image loader and saver
// - makes it easier to add additional types later on
// - makes the function easier to read
const IMAGE_HANDLERS = [
    IMAGETYPE_JPEG => [
        'load' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg',
        'quality' => 100
    ],
    IMAGETYPE_PNG => [
        'load' => 'imagecreatefrompng',
        'save' => 'imagepng',
        'quality' => 0
    ],
    IMAGETYPE_GIF => [
        'load' => 'imagecreatefromgif',
        'save' => 'imagegif'
    ]
];

/**
 * @param $src - a valid file location
 * @param $dest - a valid file target
 * @param $targetWidth - desired output width
 * @param $targetHeight - desired output height or null
 */
function createThumbnail($src, $dest, $targetWidth, $targetHeight = null) {

    // 1. Load the image from the given $src
    // - see if the file actually exists
    // - check if it's of a valid image type
    // - load the image resource

    // get the type of the image
    // we need the type to determine the correct loader
    $type = exif_imagetype($src);

    // if no valid type or no handler found -> exit
    if (!$type || !IMAGE_HANDLERS[$type]) {
        return null;
    }

    // load the image with the correct loader
    $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
        return null;
    }


    // 2. Create a thumbnail and resize the loaded $image
    // - get the image dimensions
    // - define the output size appropriately
    // - create a thumbnail based on that size
    // - set alpha transparency for GIFs and PNGs
    // - draw the final thumbnail

    // get original image width and height
    $width = imagesx($image);
    $height = imagesy($image);

    // maintain aspect ratio when no height set
    if ($targetHeight == null) {

        // get width to height ratio
        $ratio = $width / $height;

        // if is portrait
        // use ratio to scale height to fit in square
        if ($width > $height) {
            $targetHeight = floor($targetWidth / $ratio);
        }
        // if is landscape
        // use ratio to scale width to fit in square
        else {
            $targetHeight = $targetWidth;
            $targetWidth = floor($targetWidth * $ratio);
        }
    }

    // create duplicate image based on calculated target size
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    // set transparency options for GIFs and PNGs
    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

        // make image transparent
        imagecolortransparent(
            $thumbnail,
            imagecolorallocate($thumbnail, 0, 0, 0)
        );

        // additional settings for PNGs
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
    }

    // copy entire source image to duplicate image and resize
    imagecopyresampled(
        $thumbnail,
        $image,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $width, $height
    );


    // 3. Save the $thumbnail to disk
    // - call the correct save method
    // - set the correct quality level

    // save the duplicate version of the image to disk
    return call_user_func(
        IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest,
        IMAGE_HANDLERS[$type]['quality']
    );
}
if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(404);
    echo "Unauthorized";
    die();
}
$target_dir = "/uploads/";
$target_file = basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "File troppo grosso";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    $typefolder = array(
        'userpropic' => 'propics/',
        'usermedia' => 'userpics/'
    );
    $finalName = $target_file . time() . '.' .$imageFileType;
    if (isset($_POST['type'])) {
        if ($_POST['type'] == 'userpropic' && isset($_POST['id'])) {
            $pathname = SITE_ROOT . $target_dir . $typefolder[$_POST['type']] .$finalName;
            $connection = get_db();
            $sql = "UPDATE entities 
                    SET propicpath = :path
                    WHERE id = :id";
            $statement = $connection->prepare($sql);
            $statement->bindParam(':path', $finalName, PDO::PARAM_STR);
            $statement->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
            $statement->execute();
        }
        if ($_POST['type'] == 'usermedia' && isset($_POST['id'])) {
            $pathname = SITE_ROOT . $target_dir . $typefolder[$_POST['type']] .$finalName;
            $connection = get_db();
            $new_media = array(
                "entityid" => $_POST['id'],
                "mediapath" => $finalName,
                "created" => date("c")
            );
            $sql = sprintf(
                "INSERT INTO %s (%s) values (%s)",
                "entitiesmedia",
                implode(", ", array_keys($new_media)),
                ":" . implode(", :", array_keys($new_media))
            );
            $statement = $connection->prepare($sql);
            $statement->execute($new_media);
        }
    }
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $pathname)) {
        echo "The file " . escape(basename( $_FILES["fileToUpload"]["name"])) . " has been uploaded.";
        createThumbnail($pathname, SITE_ROOT . '/uploads/thumbs/' . $finalName , 200);
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
