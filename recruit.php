<?php
define( "FILE_DIR", "./profile_photo/");
require 'vendor/autoload.php';
use Dotenv\Dotenv;
/**
 * Class GoogleSheetsAPISample
 */
class GoogleSheetsAPISample {
    /**
     * @var Google_Service_Sheets
     */
    protected $service;
    /**
     * @var array|false|string
     */
    protected $spreadsheetId;
    /**
     * GoogleSheetsAPISample constructor.
     */
    public function __construct()
    {
        $dotenv = new Dotenv(__dir__);
        $dotenv->load();
        $credentialsPath = getenv('SERVICE_KEY_JSON');
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . dirname(__FILE__) . '/' . $credentialsPath);
        $this->spreadsheetId = getenv('SPREADSHEET_ID');
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $client->setApplicationName('test');
        $this->service = new Google_Service_Sheets($client);
    }
    /**
     * @param string $date
     * @param string $name
     * @param string $ruby
     * @param string $gender
     * @param string $birth_year
     * @param string $blood_type
     * @param string $phone_number
     * @param string $mail_address
     * @param string $current_job
     * @param string $job_objective_1
     * @param string $job_objective_2
     * @param string $job_objective_3
     * @param string $job_objective_4
     * @param string $job_objective_5
     * @param string $event_experience
     * @param string $height
     * @param string $selfie
     * @param string $remarks
     */
    public function append(string $date, string $name, string $ruby, string $gender, string $birth_year, string $phone_number, string $mail_address, string $current_job, string $job_objective_1, string $job_objective_2, string $job_objective_3, string $job_objective_4, string $job_objective_5, string $event_experience, string $height, string $selfie, string $remarks)
    {
        $value = new Google_Service_Sheets_ValueRange();
        $value->setValues([ 'values' => [ $date, $name, $ruby, $gender, $birth_year, $phone_number, $mail_address, $current_job, $job_objective_1, $job_objective_2, $job_objective_3, $job_objective_4, $job_objective_5, $event_experience, $height, $selfie, $remarks] ]);
        $response = $this->service->spreadsheets_values->append($this->spreadsheetId, '応募!A1', $value, [ 'valueInputOption' => 'USER_ENTERED' ] );
//        var_dump($response);
    }
}
// 変数の初期化
$page_flag = 0;
$clean = array();
$error = array();
// サニタイズ
if( !empty($_POST) ) {
	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
	}
}
if( !empty($clean['btn_confirm']) ) {
	$error = validation($clean);
	// ファイルのアップロード
	if( !empty($_FILES['attachment_file']['tmp_name']) ) {
		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);
		if( $upload_res !== true ) {
			$error[] = 'ファイルのアップロードに失敗しました。';
		} else {
			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
		}
	}
	if( empty($error) ) {
		$page_flag = 1;
		// セッションの書き込み
		session_start();
		$_SESSION['page2'] = true;		
	}
} elseif( !empty($clean['btn_submit']) ) {
	session_start();
	if( !empty($_SESSION['page2']) && $_SESSION['page2'] === true ) {
		// セッションの削除
		unset($_SESSION['page2']);
		$page_flag = 2;
		// 変数とタイムゾーンを初期化
//		$header = null;
		$body = null;
		$admin_body = null;
		$auto_reply_subject = null;
		$auto_reply_text = null;
		$admin_reply_subject = null;
		$admin_reply_text = null;
		date_default_timezone_set('Asia/Tokyo');
		
		//日本語の使用宣言
		mb_language("ja");
		mb_internal_encoding("UTF-8");
        
        $dotenv = new Dotenv(__dir__);
        $dotenv->load();
        $address = getenv('ADMIN_EMAIL');
        
        
        // 送信元
        $from = mb_encode_mimeheader("株式会社ノックス") . " <${address}>";

        // 送信元メールアドレス
        $from_mail = "${address}";

        // 送信者名
        $from_name = mb_encode_mimeheader("株式会社ノックス");

		$header = "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
        $header .= "Return-Path: " . $from_mail . " \r\n";
        $header .= "From: " . $from ." \r\n";
        $header .= "Sender: " . $from ." \r\n";
        $header .= "Reply-To: " . $from_mail . " \r\n";
        $header .= "Organization: " . $from_name . " \r\n";
        $header .= "X-Sender: " . $from_mail . " \r\n";
        $header .= "X-Priority: 3 \r\n";

		// 件名を設定
		$auto_reply_subject = 'ご応募ありがとうございます。';
	    $date = date("Y-m-d H:i");
		// 本文を設定
		$auto_reply_text =  $clean['your_name'] . " 様\n\nこの度は、ご応募頂き誠にありがとうございます。\n下記の内容でご応募を受け付けました。\n担当の者より後日ご連絡差し上げます。\n\n";

		$auto_reply_text .= "お問い合わせ日時：" . $date . "\n\n";
		$auto_reply_text .= "氏名：" . $clean['your_name'] . "\n\n";
        $auto_reply_text .= "ふりがな：" . $clean['your_ruby'] . "\n\n";
		if( $clean['gender'] === "male" ) {
			$auto_reply_text .= "性別：男性\n\n";
		} else {
			$auto_reply_text .= "性別：女性\n\n";
		}
        
        $auto_reply_text .= "生まれた年：" . $clean['birth_year'] . "年\n\n";

        $auto_reply_text .= "連絡先電話番号：" . $clean['phone_number'] . "\n\n";
        $auto_reply_text .= "メールアドレス：" . $clean['email'] . "\n\n";		
		if( $clean['current_job'] === "1" ){
			$auto_reply_text .= "現在の職業：パート・アルバイト\n\n";
		} elseif ( $clean['current_job'] === "2" ){
			$auto_reply_text .= "現在の職業：大学生\n\n";
		} elseif ( $clean['current_job'] === "3" ){
			$auto_reply_text .= "現在の職業：短大生\n\n";
		} elseif ( $clean['current_job'] === "4" ){
			$auto_reply_text .= "現在の職業：専門学生\n\n";
		} elseif( $clean['current_job'] === "5" ){
			$auto_reply_text .= "現在の職業：高校生\n\n";
		} elseif( $clean['current_job'] === "6" ){
			$auto_reply_text .= "現在の職業：会社員\n\n";
		} elseif( $clean['current_job'] === "7" ){
			$auto_reply_text .= "現在の職業：自営業\n\n";
		} elseif( $clean['current_job'] === "8" ){
			$auto_reply_text .= "現在の職業：主婦\n\n";
		} elseif( $clean['current_job'] === "9" ){
			$auto_reply_text .= "現在の職業：就職活動中\n\n";
		} elseif( $clean['current_job'] === "10" ){
			$auto_reply_text .= "現在の職業：その他\n\n";
		}
        $auto_reply_text .= "希望職種：";
        if( $clean['job_objective_1'] === "companion"){ $auto_reply_text .= 'コンパニオン  '; }
        if( $clean['job_objective_2'] === "narrator"){ $auto_reply_text .= 'ナレーター  '; }
        if( $clean['job_objective_3'] === "mc"){ $auto_reply_text .= 'MC  '; }
        if( $clean['job_objective_4'] === "model"){ $auto_reply_text .= 'モデル  '; }
        if( $clean['job_objective_5'] === "ad"){ $auto_reply_text .= 'AD,スタッフ'; }
        $auto_reply_text .= "\n\n";
            
        if( $clean['event_experience'] === "no" ){
			$auto_reply_text .= "イベント経験：なし\n\n";
		} else {
			$auto_reply_text .= "イベント経験：あり\n\n";
		} 
        $auto_reply_text .= "身長：" . $clean['height'] . "cm\n\n";
        $auto_reply_text .= "備考：" . $clean['remarks'] . "\n\n";

		$auto_reply_text .= "株式会社ノックス";
		
		// テキストメッセージをセット
//		$body = "--__BOUNDARY__\n";
//		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body = $auto_reply_text . "\n";
//		$body .= "--__BOUNDARY__\n";
	
		// ファイルを添付
//		if( !empty($clean['attachment_file']) ) {
//			$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
//			$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
//			$body .= "Content-Transfer-Encoding: base64\n";
//			$body .= "\n";
//			$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
//			$body .= "--__BOUNDARY__\n";
//		}
//	
		// 自動返信メール送信
        mb_send_mail( $clean['email'], $auto_reply_subject, $body, $header);
//        $from = new SendGrid\Email(null, $clean['email']);
//        $to = new SendGrid\Email(null, $address);
//        $content = new SendGrid\Content("text/plain", $body);
//		$mail = new SendGrid\Mail( $from, $auto_reply_subject, $to, $content);
//        $apiKey = getenv('SENDGRID_API_KEY');
//        $sg = new \SendGrid($apiKey);
//        $response = $sg->client->mail()->send()->post($mail);
//        echo $response->statusCode();
//        echo $response->headers();
//        echo $response->body();
        
        
        
		// 運営側へ送るメールの件名
		$admin_reply_subject = "【ノックスHP】より応募を受け付けました";
	
		// 本文を設定
		$admin_reply_text = "下記の内容で応募がありました。\n\n";
		$admin_reply_text .= "お問い合わせ日時：" . $date . "\n\n";
		$admin_reply_text .= "氏名：" . $clean['your_name'] . "\n\n";
        $admin_reply_text .= "ふりがな：" . $clean['your_ruby'] . "\n\n";
        $gender = null;
		if( $clean['gender'] === "male" ) {
			$admin_reply_text .= "性別：男性\n\n";
            $gender = "男性";
		} else {
			$admin_reply_text .= "性別：女性\n\n";
            $gender = "女性";
		}
        
        $admin_reply_text .= "生まれた年：" . $clean['birth_year'] . "年\n\n";
        
        $phone_number = null;
        
        $admin_reply_text .= "連絡先電話番号：" . $clean['phone_number'] . "\n\n";
        if(is_null($clean['phone_number'])) { 
            $phone_number = "回答なし";
        } else {
            $phone_number = $clean['phone_number'];
        }
        
        $admin_reply_text .= "メールアドレス：" . $clean['email'] . "\n\n";		
        $job = null;
		if( $clean['current_job'] === "1" ){
			$admin_reply_text .= "現在の職業：パート・アルバイト\n\n";
            $job = "パート・アルバイト";
		} elseif ( $clean['current_job'] === "2" ){
			$admin_reply_text .= "現在の職業：大学生\n\n";
            $job = "大学生";
		} elseif ( $clean['current_job'] === "3" ){
			$admin_reply_text .= "現在の職業：短大生\n\n";
            $job = "短大生";
		} elseif ( $clean['current_job'] === "4" ){
			$admin_reply_text .= "現在の職業：専門学生\n\n";
            $job = "専門学生";
		} elseif( $clean['current_job'] === "5" ){
			$admin_reply_text .= "現在の職業：高校生\n\n";
            $job = "高校生";
		} elseif( $clean['current_job'] === "6" ){
			$admin_reply_text .= "現在の職業：会社員\n\n";
            $job = "会社員";
		} elseif( $clean['current_job'] === "7" ){
			$admin_reply_text .= "現在の職業：自営業\n\n";
            $job = "自営業";
		} elseif( $clean['current_job'] === "8" ){
			$admin_reply_text .= "現在の職業：主婦\n\n";
            $job = "主婦";
		} elseif( $clean['current_job'] === "9" ){
			$admin_reply_text .= "現在の職業：就職活動中\n\n";
            $job = "就職活動中";
		} elseif( $clean['current_job'] === "10" ){
			$admin_reply_text .= "現在の職業：その他\n\n";
            $job = "その他";
		}
        
        $objective1 = "希望なし";
        $objective2 = "希望なし";
        $objective3 = "希望なし";
        $objective4 = "希望なし";
        $objective5 = "希望なし";
        
        $admin_reply_text .= "希望職種：";
        if( $clean['job_objective_1'] === "companion"){ 
            $admin_reply_text .= 'コンパニオン  ';
            $objective1 = '希望';
        }
        if( $clean['job_objective_2'] === "narrator"){
            $admin_reply_text .= 'ナレーター  ';
            $objective2 = '希望';
        }
        if( $clean['job_objective_3'] === "mc"){
            $admin_reply_text .= 'MC  ';
            $objective3 = '希望';
        }
        if( $clean['job_objective_4'] === "model"){
            $admin_reply_text .= 'モデル  ';
            $objective4 = '希望';
        }
        if( $clean['job_objective_5'] === "ad"){
            $admin_reply_text .= 'AD,スタッフ';
            $objective5 = '希望';
        }
        $admin_reply_text .= "\n\n";
        
        $event = null;
        if( $clean['event_experience'] === "no" ){
			$admin_reply_text .= "イベント経験：なし\n\n";
            $event = 'なし';
		} else {
			$admin_reply_text .= "イベント経験：あり\n\n";
            $event = 'あり';
		}
        $height = '回答なし';
        if( !is_null($clean['height']) ){ $height = $clean['height']; }
        $admin_reply_text .= "身長：" . $clean['height'] . "cm\n\n";
        
        $remarks = '回答なし';
        if( !is_null($clean['remarks']) ){ $remarks = $clean['remarks']; }
        $admin_reply_text .= "備考：" . $clean['remarks'] . "\n\n";
        
        $selfie = "なし";
        if( !empty($clean['attachment_file']) ) {
            $selfie = "あり";
        }   
        $admin_reply_text .= "画像：" . $selfie;
		// テキストメッセージをセット
		$body = "--__BOUNDARY__\n";
		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body .= $admin_reply_text . "\n";
		$body .= "--__BOUNDARY__\n";

		// ファイルを添付
		if( !empty($clean['attachment_file']) ) {
			$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Transfer-Encoding: base64\n";
			$body .= "\n";
			$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
			$body .= "--__BOUNDARY__\n";
		}

		// 管理者へメール送信
        mb_send_mail( 'c.for.planning@gmail.com', $admin_reply_subject, $body, $header);
//        $from_admin = new SendGrid\Email(null, $address);
//        $to_admin = new SendGrid\Email(null, $address);
//        $content = new SendGrid\Content("text/plain", $body);
//        $mail = new SendGrid\Mail( $from_admin, $admin_reply_subject, $to_admin, $content);
//        $response = $sg->client->mail()->send()->post($mail);
//        echo $response->statusCode();
//        echo $response->headers();
//        echo $response->body();
        
        // GoogleSpreadSheetに書き込み
		$customer_data = new GoogleSheetsAPISample;
        $customer_data->append( $date, $clean['your_name'], $clean['your_ruby'], $gender, $clean['birth_year'], $phone_number, $clean['email'], $job, $objective1, $objective2, $objective3, $objective4, $objective5, $event, $height, $selfie, $remarks);
	} else {
		$page_flag = 0;
	}	
}

function validation($data) {
	$error = array();
	// 氏名のバリデーション
	if( empty($data['your_name']) ) {
		$error[] = "「氏名」は必ず入力してください。";
	} elseif( 20 < mb_strlen($data['your_name']) ) {
		$error[] = "「氏名」は20文字以内で入力してください。";
	}
    // ルビのバリデーション
    if( empty( $data['your_ruby']) ) {
        $error[] = "「ふりがな」は必ず入力してください。";
    } 

	// 性別のバリデーション
	if( empty($data['gender']) ) {
		$error[] = "「性別」は必ず入力してください。";
	} elseif( $data['gender'] !== 'male' && $data['gender'] !== 'female' ) {
		$error[] = "「性別」は必ず入力してください。";
	}
    
    // 生年月日のバリデーション
    if( empty( $data['birth_year']) ) {
        $error[] = "生まれた年を選択してください。";
    }
            
	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";
	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	}
    
    // 現在の職業
    if( empty($data['current_job']) ) {
		$error[] = "「現在の職業」は必ず選択してください。";
	} 

    // イベント経験
    if( empty($data['event_experience']) ) {
		$error[] = "「イベント経験」は必ず選択してください。";
	} 

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	} elseif( (int)$data['agreement'] !== 1 ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}
    
    // 電話番号のバリデーション
    if( !empty($data['phone_number']) && !preg_match( '/^\d{10}$|^\d{11}$/', $data['phone_number'])) {
        $error[] = "「電話番号」をご確認ください。";
    }

	return $error;
}
?>

<!DOCTYPE>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="no-x">
    <meta name="viewport" content="width=device-width">
<!--
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <link rel="mask-icon" href="safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
-->
    <title>応募フォーム</title>

    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <link rel="stylesheet" media="all" href="assets/css/style.css">
    <style rel="stylesheet" type="text/css">
        .container {
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            padding: 20px 0;
            color: #4EB374;
            font-size: 122%;
            border-top: 1px solid #999;
            border-bottom: 1px solid #999;
        }

        input[type=text],
        input[type=number]{
            padding: 5px 10px;
            font-size: 86%;
            border: none;
            border-radius: 3px;
            background: #ddf0ff;
        }

        input[name=btn_confirm],
        input[name=btn_submit],
        input[name=btn_back] {
            margin-top: 10px;
            padding: 5px 20px;
            font-size: 100%;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            box-shadow: 0 3px 0 #2887d1;
            background: #4eaaf1;
        }
        
        button[name=btn_back] {
            margin-top: 10px;
            padding: 5px 20px;
            font-size: 100%;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            box-shadow: 0 3px 0 #2887d1;
            background: #4eaaf1;
            margin-right: 20px;
            box-shadow: 0 3px 0 #777;
            background: #999;
        }

        .element_wrap {
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        label {
            display: inline-block;
            margin-bottom: 10px;
            font-weight: bold;
            width: 250px;
            vertical-align: top;
        }

        .element_wrap p {
            display: inline-block;
            margin: 0;
            text-align: left;
        }

        label[for=gender_male],
        label[for=gender_female],
        label[for=agreement],
        label[for=event_experience],
        label[for=no_event_experience],
        label[for=companion],
        label[for=narrator],
        label[for=mc],
        label[for=model],
        label[for=ad]{
            margin-right: 10px;
            width: auto;
            font-weight: normal;
        }

        textarea[name=remarks] {
            padding: 5px 10px;
            width: 60%;
            height: 100px;
            font-size: 86%;
            border: none;
            border-radius: 3px;
            background: #ddf0ff;
        }

        .error_list {
            padding: 10px 30px;
            color: #ff2e5a;
            font-size: 86%;
            text-align: left;
            border: 1px solid #ff2e5a;
            border-radius: 5px;
        }

        @media only screen and (min-width: 800px) {
            #sidebar h2 {
                padding: 30px 0;
            }
        }

        #privacy {
            padding: 10px 30px;
            color: #000;
            font-size: 86%;
            text-align: left;
            border: 1px solid #000;
            border-radius: 5px;
            margin: 20px 0;
        }

        #privacy h3 {
            text-align: center;
            font-weight: none;
        }

        #birth {
            display:flex;
        }
        /* TopHeader  */
	#topheader {
/*		position: fixed;*/
/*		z-index: 10000;*/
		left: 0;
		top: 0;
		width: 100%;
		background: rgba(255, 255, 255, 0.25);
		height: 6em;
		line-height: 3em;

	}

    #logo .headerimg {
        display: block;
        max-width: 80%;
        margin-left: 2%;
        max-height: 6em;
        margin: 0 auto;
    }


		#topheader nav {
			position: absolute;
			right: 0.5em;
            top: 0.5em;
			height: 6em;
			line-height: 3em;
		}

			#topheader nav ul {
				margin: 0;
			}

				#topheader nav ul li {
					display: inline-block;
					margin-left: 0.5em;
					font-size: 0.9em;
				}

					#topheader nav ul li a {
						display: block;
						color: inherit;
						text-decoration: none;
						height: 3em;
						line-height: 3em;
						padding: 0 0.5em 0 0.5em;
						outline: 0;
					}

		@media screen and (max-width: 736px) {

			#topheader {
				height: 6em;
				line-height: 2.5em;
			}

				#logo .headerimg {
                    display: block;
                    margin-left: auto;
                    margin-right: auto;
                }

				#topheader nav {
					display: none;
				}

		}

    </style>
</head>

<body id="top">
        <div id="content" class="container">
        <header id="topheader">
        <p id="logo"><a href="index.php#"><img src="./images/logos/nox_logo_touka.png" class="headerimg"></a></p>
        </header>
        <h1>応募フォーム</h1>
        <?php if( $page_flag === 1 ): ?>

        <form method="post" action="">
            <div class="element_wrap">
                <label>氏名</label>
                <p>
                    <?php echo $clean['your_name']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>ふりがな</label>
                <p>
                    <?php echo $clean['your_ruby']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>性別</label>
                <p>
                    <?php if( $clean['gender'] === "male" ){ echo '男性'; }else{ echo '女性'; } ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>生まれた年</label>
                <p>
                    <?php  echo $clean['birth_year']; ?>年
                </p>
            </div>

            <div class="element_wrap">
                <label>連絡先電話番号（ハイフンなし）</label>
                <p>
                    <?php echo $clean['phone_number']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>メールアドレス</label>
                <p>
                    <?php echo $clean['email']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>現在の職業</label>
                <p>
                    <?php if( $clean['current_job'] === "1" ){ echo 'パート・アルバイト'; }
                    elseif( $clean['current_job'] === "2" ){ echo '大学生'; }
                    elseif( $clean['current_job'] === "3" ){ echo '短大生'; }
                    elseif( $clean['current_job'] === "4" ){ echo '専門学生'; }
                    elseif( $clean['current_job'] === "5" ){ echo '高校生'; }
                    elseif( $clean['current_job'] === "6" ){ echo '会社員'; }
                    elseif( $clean['current_job'] === "7" ){ echo '自営業'; }
                    elseif( $clean['current_job'] === "8" ){ echo '主婦'; } 
                    elseif( $clean['current_job'] === "9" ){ echo '就職活動中'; } 
                    elseif( $clean['current_job'] === "10" ){ echo 'その他'; }  ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>希望職種（複数回答可）</label>
                <p>
                    <?php if( $clean['job_objective_1'] === "companion"){ echo 'コンパニオン  '; }
                    if( $clean['job_objective_2'] === "narrator"){ echo 'ナレーター  '; }
                    if( $clean['job_objective_3'] === "mc"){ echo 'MC  '; }
                    if( $clean['job_objective_4'] === "model"){ echo 'モデル  '; }
                    if( $clean['job_objective_5'] === "ad"){ echo 'AD,スタッフ'; } ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>イベント経験</label>
                <p>
                    <?php if( $clean['event_experience'] === "no" ){ echo 'なし'; }else{ echo 'あり'; } ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>身長</label>
                <p>
                    <?php echo $clean['height']; ?>cm
                </p>
            </div>
            <div class="element_wrap">
                <label>備考</label>
                <p>
                    <?php echo nl2br($clean['remarks']); ?>
                </p>
            </div>

            <?php if( !empty($clean['attachment_file']) ): ?>
            <div class="element_wrap">
                <label>ご自身の画像ファイルの添付<br>※コンパニオン、MC、モデルをご希望の場合</label>
                <p><img src="<?php echo FILE_DIR.$clean['attachment_file']; ?>"></p>
            </div>
            <?php endif; ?>

            <div class="element_wrap">
                <label>プライバシーポリシーに同意する</label>
                <p>
                    <?php if( $clean['agreement'] === "1" ){ echo '同意する'; }else{ echo '同意しない'; } ?>
                </p>
            </div>
            <input type="submit" name="btn_back" value="戻る">
            <input type="submit" name="btn_submit" value="送信">
            <input type="hidden" name="your_name" value="<?php echo $clean['your_name']; ?>">
            <input type="hidden" name="your_ruby" value="<?php echo $clean['your_ruby']; ?>">
            <input type="hidden" name="gender" value="<?php echo $clean['gender']; ?>">
            <input type="hidden" name="birth_year" value="<?php echo $clean['birth_year']; ?>">
            <?php if( !empty($clean['phone_number']) ): ?>
            <input type="hidden" name="phone_number" value="<?php echo $clean['phone_number']; ?>">
            <?php endif; ?>
            <input type="hidden" name="email" value="<?php echo $clean['email']; ?>">
            <input type="hidden" name="current_job" value="<?php echo $clean['current_job']; ?>">
            <?php if( !empty($clean['job_objective_1']) ): ?>
            <input type="hidden" name="job_objective_1" value="<?php echo $clean['job_objective_1']; ?>">
            <?php endif; ?>
            <?php if( !empty($clean['job_objective_2']) ): ?>
            <input type="hidden" name="job_objective_2" value="<?php echo $clean['job_objective_2']; ?>">
            <?php endif; ?>
            <?php if( !empty($clean['job_objective_3']) ): ?>
            <input type="hidden" name="job_objective_3" value="<?php echo $clean['job_objective_3']; ?>">
            <?php endif; ?>
            <?php if( !empty($clean['job_objective_4']) ): ?>
            <input type="hidden" name="job_objective_4" value="<?php echo $clean['job_objective_4']; ?>">
            <?php endif; ?>
            <?php if( !empty($clean['job_objective_5']) ): ?>
            <input type="hidden" name="job_objective_5" value="<?php echo $clean['job_objective_5']; ?>">
            <?php endif; ?>
            <input type="hidden" name="event_experience" value="<?php echo $clean['event_experience']; ?>">
            <input type="hidden" name="height" value="<?php echo $clean['height']; ?>">
            <?php if( !empty($clean['attachment_file']) ): ?>
            <input type="hidden" name="attachment_file" value="<?php echo $clean['attachment_file']; ?>">
            <?php endif; ?>
            <input type="hidden" name="remarks" value="<?php echo $clean['remarks']; ?>">
            <input type="hidden" name="agreement" value="<?php echo $clean['agreement']; ?>">
        </form>

        <?php elseif( $page_flag === 2 ): ?>

        <p>送信が完了しました。</p>
        <p><a href="index.php#">トップに戻る</a></p>

        <?php else: ?>
        <div id="privacy">
            <h3>プライバシーポリシー</h3>
            <p>ご登録いただく前に、必ず下記の「登録情報の取り扱いに関する確認事項」
                をご確認お願い致します。</p>
            <p>登録フォームを送信頂いた時は、以下の確認事項に同意頂いたものとさせて
                頂きます。 </p>
            <br>

            <h3 style="color: red; font-weight: none;">＜ 登録情報の取り扱いに関する確認事項 ＞</h3>
            <p>■個人情報の取得および利用等は、就業の確保を図るものと、
                適切な雇用管理を行います。 </p>
            <p>■法令に基づく場合や、当社ノックスの業務関係以外は、
                取得した個人情報を本人の同意無しに第三者に提供はしません。</p>

            <br>
            <p>上記内容に関するお問い合わせは、下記までお問い合わせ下さい。</p>

            株式会社　ノックス <br>
            TEL 03-6264-8190
        </div>
        <?php if( !empty($error) ): ?>
        <ul class="error_list">
            <?php foreach( $error as $value ): ?>
            <li>
                <?php echo $value; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <form method="post" action="" enctype="multipart/form-data">
            *は必須項目です
            <div class="element_wrap">
                <label>*氏名</label>
                <input type="text" name="your_name" value="<?php if( !empty($clean['your_name']) ){ echo $clean['your_name']; } ?>">
            </div>
            <div class="element_wrap">
                <label>*ふりがな</label>
                <input type="text" name="your_ruby" value="<?php if( !empty($clean['your_ruby']) ){ echo $clean['your_ruby']; } ?>">
            </div>
            <div class="element_wrap">
                <label>*性別</label>
                <label for="gender_female"><input id="gender_female" type="radio" name="gender" value="female" <?php if( !empty($clean['gender']) && $clean['gender']==="female" ){ echo 'checked' ; } ?>>女性</label>
                <label for="gender_male"><input id="gender_male" type="radio" name="gender" value="male" <?php if( !empty($clean['gender']) && $clean['gender']==="male" ){ echo 'checked' ; } ?>>男性</label>
            </div>
            <div class="element_wrap" id="birth">
                <label>*生まれた年</label>
                <select name="birth_year">
                    <option value="">-</option>
                    <?php foreach (range(1980, 2005) as $year) : ?>
                    <option value="<?=$year?>" <?php if( !empty($clean['birth_year']) && $clean['birth_year']==="${year}" ){ echo 'selected' ; } ?>><?=$year?></option>
                    <?php endforeach; ?>
                </select>　年
            </div>
            <div class="element_wrap">
                <label>連絡先電話番号（ハイフンなし）</label>
                <input type="number" pattern="\d*" name="phone_number" value="<?php if( !empty($clean['phone_number']) ){ echo $clean['phone_number']; } ?>">
                
            </div>
            <div class="element_wrap">
                <label>*メールアドレス</label>
                <input type="text" name="email" value="<?php if( !empty($clean['email']) ){ echo $clean['email']; } ?>">
            </div>
            
            <div class="element_wrap">
                <label>*現在の職業</label>
                <select name="current_job">
                    <option value="">選択してください</option>
                    <option value="1" <?php if( !empty($clean['current_job']) && $clean['current_job']==="1" ){ echo 'selected' ; } ?>>パート・アルバイト</option>
                    <option value="2" <?php if( !empty($clean['current_job']) && $clean['current_job']==="2" ){ echo 'selected' ; } ?>>大学生</option>
                    <option value="3" <?php if( !empty($clean['current_job']) && $clean['current_job']==="3" ){ echo 'selected' ; } ?>>短大生</option>
                    <option value="4" <?php if( !empty($clean['current_job']) && $clean['current_job']==="4" ){ echo 'selected' ; } ?>>専門学生</option>
                    <option value="5" <?php if( !empty($clean['current_job']) && $clean['current_job']==="5" ){ echo 'selected' ; } ?>>高校生</option>
                    <option value="6" <?php if( !empty($clean['current_job']) && $clean['current_job']==="6" ){ echo 'selected' ; } ?>>会社員</option>
                    <option value="7" <?php if( !empty($clean['current_job']) && $clean['current_job']==="7" ){ echo 'selected' ; } ?>>自営業</option>
                    <option value="8" <?php if( !empty($clean['current_job']) && $clean['current_job']==="8" ){ echo 'selected' ; } ?>>主婦</option>
                    <option value="9" <?php if( !empty($clean['current_job']) && $clean['current_job']==="9" ){ echo 'selected' ; } ?>>就職活動中</option>
                    <option value="10" <?php if( !empty($clean['current_job']) && $clean['current_job']==="10" ){ echo 'selected' ; } ?>>その他</option>
                </select>
            </div>
            
            <div class="element_wrap">
                <label>希望職種（複数回答可）</label>
                <label for="companion"><input type="checkbox" class="ib" name="job_objective_1" value="companion" <?php if( !empty ($clean['job_objective_1']) && $clean['job_objective_1'] === "companion"){ echo 'checked' ; } ?>>コンパニオン</label>
                <label for="narrator"><input type="checkbox" class="ib" name="job_objective_2" value="narrator" <?php if( !empty ($clean['job_objective_2']) && $clean['job_objective_2'] === "narrator"){ echo 'checked' ; } ?>>ナレーター</label>
                <label for="mc"><input type="checkbox" class="ib" name="job_objective_3" value="mc" <?php if( !empty ($clean['job_objective_3']) && $clean['job_objective_3'] === "mc"){ echo 'checked' ; } ?>>MC</label>
                <label for="model"><input type="checkbox" class="ib" name="job_objective_4" value="model" <?php if( !empty ($clean['job_objective_4']) && $clean['job_objective_4'] === "model"){ echo 'checked' ; } ?>>モデル</label>
                <label for="ad"><input type="checkbox" class="ib" name="job_objective_5" value="ad" <?php if( !empty ($clean['job_objective_5']) && $clean['job_objective_5'] === "ad"){ echo 'checked' ; } ?>>AD,スタッフ</label>
            </div>
            
            <div class="element_wrap">
                <label>*イベント経験</label>
                <label for="event_experience"><input id="event_experience" type="radio" name="event_experience" value="experience" <?php if( !empty($clean['event_experience']) && $clean['event_experience']==="experience" ){ echo 'checked' ; } ?>>あり</label>
                <label for="no_event_experience"><input id="no_event_experience" type="radio" name="event_experience" value="no" <?php if( !empty($clean['event_experience']) && $clean['event_experience']==="no" ){ echo 'checked' ; } ?>>なし</label>
            </div>
            
            <div class="element_wrap">
                <label>身長</label>
                <input type="number" step="0.1" name="height" value="<?php if( !empty($clean['height']) ){ echo $clean['height']; } ?>">cm
            </div>
            <div class="element_wrap">
                <label>備考</label>
                <textarea name="remarks"><?php if( !empty($clean['remarks']) ){ echo $clean['remarks']; } ?></textarea>
            </div>
            
            <div class="element_wrap">
                <label>ご自身の画像ファイルの添付<br>※コンパニオン、MC、モデルをご希望の場合</label>
                <input type="file" name="attachment_file">
                <p style="color: red;">■次の確認画面で写真が表示されない場合は、写真が送られません。<br> 写真の解像度を落としてから再度お試しください。（上限2MB）<br> それでも表示されない場合は、弊社からの送信完了通知メール宛に折り返し添付し、送付してください。</p>
            </div>

            <div class="element_wrap">
                <label for="agreement"><input id="agreement" type="checkbox" name="agreement" value="1" <?php if( !empty($clean['agreement']) && $clean['agreement']==="1" ){ echo 'checked' ; } ?>><a href="#privacy">プライバシーポリシー</a>に同意する</label>
            </div>
            <input type="submit" name="btn_confirm" value="入力内容を確認する">
            <button type="button" name="btn_back" onclick="location.href='index.php#'">戻る</button>
        </form>


        <?php endif; ?>
        <footer id="footer">
            <div class="footC">
                Copyright(c) NO-X Inc. All Rights Reserved. 
            </div>
        </footer>
    </div>

</body>

</html>
