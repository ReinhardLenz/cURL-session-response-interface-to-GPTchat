<?php

include('../templates/header.php');
//start new mysql related
include('connect.php'); // Include the database connection file
//end new mysql related

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


error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // only if using Composer

use PhpOffice\PhpSpreadsheet\IOFactory; // Import the IOFactory class from PhpSpreadsheet


$prompt = $_POST['prompt'] ?? '';


//start new mysql related

$sql = "INSERT INTO ChatGPT (content) VALUES ('$prompt')";
$result = mysqli_query($con, $sql);

if ($result) {
    echo "<p>Data inserted successfully.</p>";
    //header('location: display.php');
} else {
    throw new RuntimeException('mysqli query error: ' . mysqli_error($con));
}
//end new mysql related

/* print "<pre>";
print_r($_POST);
print "</pre>"; */

$uploaddir = "uploads/";

$uploadfile = $uploaddir . $_FILES['filetto']['name'];
print "<pre>";
if (move_uploaded_file($_FILES['filetto']['tmp_name'], $uploadfile)) {
/*     echo "Kopioitiin tiedosto: {$_FILES['filetto']['name']}\n";
    echo "nimelle: $uploadfile\n\n";
     echo "Tiedosto näkyy kansiossa: ";
     echo "<a href='$uploaddir'>$uploaddir</a><br>\n";
   print "Muuta informaatiota:\n"; */
} else {
   print "Tiedoston kopioiminen epäonnistui, Muuta informaatiota:\n";
}
/* print_r($_FILES);
print "</pre>"; */

$filePath = __DIR__ ."/". $uploadfile;

// Get the file extension in lowercase
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

/* print"<pre>";
print_r($filePath);
print "</pre>"; */


$fileContent = '';

if (file_exists($filePath)) {
    if ($ext === 'txt') {
        // Handle .txt file
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            die("Error reading file content.");
        }
        $fileContent = strip_tags($fileContent); // remove HTML if any
        $fileContent = substr($fileContent, 0, 3000); 

    } elseif ($ext === 'xlsx') {
        // Handle .xlsx file
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach ($rows as $row) {
                $fileContent .= implode("\t", $row) . "\n";
            }
        } catch (Exception $e) {
            $fileContent = "[Error reading Excel file: " . $e->getMessage() . "]";
        }

    } else {
        $fileContent = "[Unsupported file type: .$ext]";
    }
} else {
    $fileContent = "[File not found]";
}

$combinedPrompt = <<<EOT
You are given additional context from an Excel file, followed by a user question.

Context:

$fileContent

Now, using this context, answer the following user question:

$prompt
EOT;



/* print "<pre>";
print "<br>";
print"<p>------------</p>";

print_r($combinedPrompt);
print"<p>------------</p>";
print "<br>";
print "</pre>"; */

$data = [
    "model" => "gpt-4",
    "messages" => [
        ["role" => "user", "content" => $combinedPrompt]
    ]
]; // Prepare the data for the API request

$ch = curl_init('https://api.openai.com/v1/chat/completions'); // Initialize cURL session
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set the option to return the response as a string
curl_setopt($ch, CURLOPT_POST, true);// Set the request method to POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer sk-proj-W_ _ _ _',
    'Content-Type: application/json'
]);// Set the headers for the request
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Set the POST fields as JSON
$response = curl_exec($ch); // Execute the cURL request
curl_close($ch); // Close the cURL session

$json = json_decode($response, true); // Decode the JSON response
#echo $json["choices"][0]["message"]["content"] ?? "Error parsing response";
#echo "<strong>Response:</strong><br>" . htmlspecialchars($json["choices"][0]["message"]["content"]) . "<br>";
$text=$json["choices"][0]["message"]["content"];//  htmlspecialchars the response content to prevent XSS
echo "<div style='width: 100%; word-wrap: break-word; white-space: normal;'>".nl2br(htmlspecialchars($text))."</div>";
#echo "<pre>".htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT))."</pre>";

include('../templates/footer.php');
?>
</body>
</html>