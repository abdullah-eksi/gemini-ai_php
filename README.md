

# Gemini Ai Zeki Chatbot

## Introduction
Zeki is an interactive chatbot implemented in PHP, designed to respond to user queries. It handles predefined questions and uses the Gemini AI API to generate responses for more complex or undefined queries. 

## Features
- **Predefined Responses**: Provides instant answers to a set of predefined questions.
- **AI-Generated Responses**: For queries that don't match predefined questions, it uses the Gemini AI API to generate appropriate responses.
- **Random Responses for Specific Keywords**: When specific keywords like "Chatgpt" are detected, it randomly selects from a set of predefined responses.

## Prerequisites
- A web server with PHP support.
- An API key for the Gemini AI API.

## Setup
1. **Clone the Repository**
    ```bash
    git clone https://github.com/your-repository/zeki-chatbot.git
    cd zeki-chatbot
    ```

2. **Configure the API Key**
   Replace `'YOUR_GEMINI_API_KEY'` with your actual Gemini AI API key in the PHP file.

## File Structure
- `index.php`: The main PHP file that handles user input and generates responses.

## Usage
1. **Start the Web Server**
   Ensure your web server is running and configured to serve the `index.php` file.

2. **Access the Chatbot**
   Open your web browser and navigate to the URL where the chatbot is hosted.

3. **Interact with Zeki**
   - Enter your question in the input field and click the "Gönder" button.
   - The chatbot will respond based on predefined questions or by generating a response using the Gemini AI API.

## Code Explanation

### HTML Form
The HTML form in `index.php` allows users to input their questions.

```html
<form class="" action="" method="post">
    <input type="text" required name="soru">
    <button type="submit" name="sor">Gönder</button>
</form>
```

### PHP Script
The PHP script processes the user input, checks for predefined questions, and if necessary, queries the Gemini AI API for a response.

#### Time Variable
Sets the time for time-related queries.

```php
date_default_timezone_set('Europe/Istanbul');
$saat = "Şuanda Saat " . date("H:i");
```

#### Predefined Questions
An associative array of predefined questions and their corresponding responses.

```php
$qa_pairs = array(
    'Adın Ne' => 'Benim Adım Zeki',
    'Sen Kimsin' => 'Ben Senin Yardımcı Robotunum',
    ...
    'Saat Kaç' => $saat
);
```

#### Random Responses for Specific Keywords
An array of responses for queries containing the keyword "Chatgpt".

```php
$gptycevap = array(
    '1' => 'Tanıyorum iyi bir dosttur kendisi',
    '2' => 'Buyrun Ben Yardımcı Olayım Ben Robot Zeki '
);
```

#### Processing User Input
Cleans the user input by removing special characters and extra spaces.

```php
$user_message = $_POST["soru"];
$user_message = preg_replace("/[^a-zA-ZıİiIğĞüÜşŞöÖçÇ0-9]+/u", " ", $user_message);
$user_message = trim($user_message);
```

#### Checking for Keyword and Predefined Questions
Checks if the user input contains the keyword "Chatgpt" and responds with a random answer from the `gptycevap` array. If not, it checks for predefined questions.

```php
if (stripos($user_message, 'Chatgpt') !== false) {
    $rand = array_rand($gptycevap);
    $response = $gptycevap[$rand];
} else {
    $response = null;
    foreach ($qa_pairs as $question => $answer) {
        ...
        if ($similarity >= 60) {
            $response = $answer;
            break;
        }
    }
```

#### Querying the Gemini AI API
If no predefined answer is found, the script sends a request to the Gemini AI API to generate a response.

```php
if ($response === null) {
    $data = array(
        "contents" => array(
            array(
                "role" => "user",
                "parts" => array(
                    array(
                        "text" => $user_message
                    )
                )
            )
        ),
        ...
    );

    $json_data = json_encode($data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        $response = "Üzgünüm Bu Konu Hakkında Bir Bilgim Yok tekrar deneyin";
    } else {
        $json_response = json_decode($response, true);

        if (!isset($json_response['generations'][0]['content'])) {
            $response = "Üzgünüm Bu Konu Hakkında Bir Bilgim Yok tekrar deneyin";
        } else {
            $response = $json_response['generations'][0]['content'];
        }
    }

    curl_close($ch);
}
```

### Displaying the Response
The generated response is displayed to the user.

```php
echo "<hr>";
echo $response;
```

## Conclusion
This chatbot provides a basic framework for interacting with users using both predefined answers and AI-generated responses. You can further enhance its functionality by adding more predefined questions, improving response handling, and integrating additional AI capabilities.

