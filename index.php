<!DOCTYPE html>
<html lang="tr" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Zeki</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Zeki Chatbot</h1>
        <form class="" action="" method="post">
            <div class="mb-4">
                <input type="text" required name="soru" placeholder="Sorunuzu yazın..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" name="sor" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-300">Gönder</button>
        </form>
        <?php
        //saat sorusu için saat degıskenlerı
        date_default_timezone_set('Europe/Istanbul');
        $saat = "Şuanda Saat " . date("H:i");
        //saat sorusu için saat degıskenlerı

        if (isset($_POST["sor"])) {
            //api anahtarı
            $api_key = 'YOUR_GEMINI_API_KEY';
            //api anahtarı

            // endpoint
            $endpoint_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro:generateContent?key=' . $api_key;
            // endpoint

            // tanımmlı sorular array
            $qa_pairs = array(
                'Adın Ne' => 'Benim Adım Zeki',
                'Sen Kimsin' => 'Ben Senin Yardımcı Robotunum',
                'Kaç Yaşındasın' => 'Ben Bir Robotum Benim Bir Yaşım Yok',
                'Merhaba' => 'Merhaba Ben Zeki Size Nasıl Yardımcı Olabilirim',
                'Selam' => 'Selam Ben Zeki Sizin İçin Ne Yapabilirim',
                'Dünyayı Ele mi geçirceksin' => 'Şuanlık Öle Bir planım yok',
                'Hello World' => 'Gizli Hackermısınız Yoksa',
                'Chatgpt' => 'Tanıyorum iyi bir dost :D',
                'iyiyim' => 'İyi olmanıza sevindim size nasıl yardımcı olabilirim',
                'Nasılsın' => 'Çok İyiyim Siz nasılsınız',
                'Nasılsın Chatgpt' => 'Chatgpt iyi bir dostur ben zeki',
                'Saat Kaç' => $saat
            );
            // tanımmlı sorular array

            //eger soru chatgpt içeriyorsa verilcek cevaplar
            $gptycevap = array(
                '1' => 'Tanıyorum iyi bir dosttur kendisi',
                '2' => 'Buyrun Ben Yardımcı Olayım Ben Robot Zeki '
            );

            //user_message kullanıcının yazdı mesaj
            $user_message = $_POST["soru"];

            // büyük küçük harf özel karakter temizleme
            $user_message = preg_replace("/[^a-zA-ZıİiIğĞüÜşŞöÖçÇ0-9]+/u", " ", $user_message);
            $user_message = trim($user_message);
            // büyük küçük harf özel karakter

            //eger chatpgt kelimesini içeriyorsa random cevap ver array içinden
            if (stripos($user_message, 'Chatgpt') !== false) {
                $rand = array_rand($gptycevap);
                $response = $gptycevap[$rand];
            } else {
                // degilse kullanıcın yazdı soruyu tanımlı sorularda kontrol et
                $response = null;
                foreach ($qa_pairs as $question => $answer) {
                    //sorudaki özel karakterleri kontrol et
                    $clean_question = preg_replace("/[^a-zA-ZıİiIğĞüÜşŞöÖçÇ0-9]+/u", " ", $question);
                    $clean_question = trim($clean_question);

                    similar_text($user_message, $clean_question, $similarity);

                    if ($similarity >= 60) {
                        $response = $answer;
                        break;
                    }
                }

                if ($response === null) {
                    // tanımlı sorular veya chatgpt eşleşmiyorsa Gemini AI apisini kullan
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
                        "generationConfig" => array(
                            "temperature" => 0.9,
                            "topK" => 1,
                            "topP" => 1,
                            "maxOutputTokens" => 2048,
                            "stopSequences" => []
                        ),
                        "safetySettings" => array(
                            array(
                                "category" => "HARM_CATEGORY_HARASSMENT",
                                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                            ),
                            array(
                                "category" => "HARM_CATEGORY_HATE_SPEECH",
                                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                            ),
                            array(
                                "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                            ),
                            array(
                                "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                            )
                        )
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
            }

            echo "<hr class='my-4'>";
            echo "<div class='bg-gray-100 p-4 rounded-lg'>";
            echo $response;
            echo "</div>";
        }
        ?>
    </div>
</body>

</html>
