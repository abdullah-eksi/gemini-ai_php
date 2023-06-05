<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Zeki</title>
</head>

<body>
    <form class="" action="" method="post">
        <input type="text" required name="soru">
        <button type="submit" name="sor">Gönder</button>
    </form>
</body>

</html>


<?php

//saat sorusu için saat degıskenlerı
date_default_timezone_set('Europe/Istanbul');
$saat = "Şuanda Saat " . date("H:i");
//saat sorusu için saat degıskenlerı



if (isset($_POST["sor"])) {
    //api anahtarı
    $api_key = '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';
    //api anahtarı

    // endpoint

    /*
    endpoit bunları denedim üstekinde endpoint hatası diyor alttakınde limit hatası internette bu endpointleri buldum
    $endpoint_url = 'https://api.openai.com/v1/engines/davinci-codex/completions';
    $endpoint_url = 'https://api.openai.com/v1/engines/codex/completions';
    */

    $endpoint_url = 'https://api.openai.com/v1/engines/codex/completions';
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
        '1' => 'Chatgpt iyi bir dostur ben zeki',
        '2' => 'Tanıyorum iyi bir dosttur kendisi',
        '3' => 'Buyrun Ben Yardımcı Olayım Ben Robot Zeki '
    );

    //eger soru chatgpt içeriyorsa verilcek cevaplar

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

        //eger chatpgt kelimesini içeriyorsa random cevap ver array içinden
    } else {
        // degilse kullanıcın yazdı soruyu tanımlı sorularda kontrol et
        foreach ($qa_pairs as $question => $answer) {
            //sorudaki özel karakterleri kontrol et
            $clean_question = preg_replace("/[^a-zA-ZıİiIğĞüÜşŞöÖçÇ0-9]+/u", " ", $question);
            $clean_question = trim($clean_question);


            similar_text($user_message, $clean_question, $similarity);

            //sorudaki özel karakterleri kontrol et benzerliği ayarla 
            if ($similarity >= 60) {
                $response = $answer;
                break;
            } else {
                // tanımlı sorular veya chatgpt eşleşmiyorsa openai apisini kullan
                $data = array(
                    'prompt' => $user_message,
                    'max_tokens' => 50,
                    'temperature' => 0.5,
                    'n' => 1,
                    'stop' => "\n"
                );


                $json_data = json_encode($data);

                $ch = curl_init();


                curl_setopt($ch, CURLOPT_URL, $endpoint_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key
                )
                );


                $response = curl_exec($ch);


                // eger api isteğinde sıkıntı olduysa hata mesajı gondermek yerıne yapay zeka sasırtması gonder
                if ($response === false) {
                    $response = "Üzgünüm Bu Konu Hakkında Bir Bilgim Yok tekrar deneyin ";
                } else {

                    $json_response = json_decode($response, true);

                    // eger api isteğinde sıkıntı olduysa hata mesajı gondermek yerıne yapay zeka sasırtması gonder
                    if (!isset($json_response['choices'][0]['text'])) {
                        $response = "Üzgünüm Bu Konu Hakkında Bir Bilgim Yok tekrar deneyin";
                    } else {
                        // bot tarafından donen mesajı parçala
                        $generated_text = $json_response['choices'][0]['text'];
                        // bot tarafından donen mesajı parçala
                        $response = $generated_text;
                    }


                }

            }
        }
    }

    echo "<hr>";
    // bot tarafından gelen mesajı yaz
    echo $response;
    // bot tarafından gelen mesajı yaz
}


?>