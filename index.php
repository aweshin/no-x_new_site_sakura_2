<?php

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
     * @param string $company
     * @param string $email
     * @param string $tel
     * @param string $message
     */
    public function append(string $date, string $name, string $company, string $email, string $tel, string $message)
    {
        $value = new Google_Service_Sheets_ValueRange();
        $value->setValues([ 'values' => [ $date, $name, $company, $email, $tel, $message ] ]);
        $response = $this->service->spreadsheets_values->append($this->spreadsheetId, 'お問い合わせ!A1', $value, [ 'valueInputOption' => 'USER_ENTERED' ] );
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
//	if( !empty($_FILES['attachment_file']['tmp_name']) ) {
//		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);
//		if( $upload_res !== true ) {
//			$error[] = 'ファイルのアップロードに失敗しました。';
//		} else {
//			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
//		}
//	}
	if( empty($error) ) {
		$page_flag = 1;
		// セッションの書き込み
		session_start();
		$_SESSION['page'] = true;		
	}
} elseif( !empty($clean['btn_submit']) ) {
	session_start();
	if( !empty($_SESSION['page']) && $_SESSION['page'] === true ) {
		// セッションの削除
		unset($_SESSION['page']);
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
		$header .= "Content-Type: text/plain \r\n";
        $header .= "Return-Path: " . $from_mail . " \r\n";
        $header .= "From: " . $from ." \r\n";
        $header .= "Sender: " . $from ." \r\n";
        $header .= "Reply-To: " . $from_mail . " \r\n";
        $header .= "Organization: " . $from_name . " \r\n";
        $header .= "X-Sender: " . $from_mail . " \r\n";
        $header .= "X-Priority: 3 \r\n";

		// 件名を設定
		$auto_reply_subject = 'お問い合わせありがとうございます。';
	    $date = date("Y-m-d H:i");
		// 本文を設定
		$auto_reply_text = "この度は、お問い合わせ頂き誠にありがとうございます。\n下記の内容でお問い合わせを受け付けました。\n\n";
		$auto_reply_text .= "お問い合わせ日時：" . $date . "\n\n";
        $auto_reply_text .= "御社名：" . $clean['your_company'] . "\n\n";
		$auto_reply_text .= "氏名：" . $clean['your_name'] . "\n\n";
		$auto_reply_text .= "メールアドレス：" . $clean['email'] . "\n\n";
		$auto_reply_text .= "電話番号：" . $clean['tel'] . "\n\n";
	

		$auto_reply_text .= "お問い合わせ内容：" . $clean['message'] . "\n\n";
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
		$admin_reply_subject = "お問い合わせを受け付けました";
	
		// 本文を設定
		$admin_reply_text = "下記の内容でお問い合わせがありました。\n\n";
		$admin_reply_text .= "お問い合わせ日時：" . $date . "\n\n";
        $admin_reply_text .= "御社名：" . $clean['your_company'] . "\n\n";
		$admin_reply_text .= "氏名：" . $clean['your_name'] . "\n\n";
		$admin_reply_text .= "メールアドレス：" . $clean['email'] . "\n\n";
		$admin_reply_text .= "電話番号：" . $clean['tel'] . "\n\n";
	

		$admin_reply_text .= "お問い合わせ内容：" . $clean['message'] . "\n\n";
		
		// テキストメッセージをセット
//		$body = "--__BOUNDARY__\n";
//		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body = $admin_reply_text . "\n";
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
        $customer_data->append( $date, $clean['your_name'], $clean['your_company'], $clean['email'], $clean['tel'], $clean['message']);
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
    
	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";
	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	}

	// お問い合わせ内容のバリデーション
	if( empty($data['message']) ) {
		$error[] = "「お問い合わせ内容」は必ず入力してください。";
	}

    // 電話番号のバリデーション
    if( !empty($data['tel']) && !preg_match( '/^\d{10}$|^\d{11}$/', $data['tel'])) {
        $error[] = "「電話番号」をご確認ください。";
    }
    
	return $error;
}
?>


<!DOCTYPE HTML>
<!--
	Overflow by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

<head>
    <title>NO-X</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript>
        <link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <style rel="stylesheet" type="text/css">
        .element_wrap {
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        .element_wrap p {
            display: inline-block;
            margin: 0;
            text-align: left;
        }

        .error_list {
            padding: 10px 30px;
            color: #ff2e5a;
            font-size: 86%;
            text-align: left;
            border: 1px solid #ff2e5a;
            border-radius: 5px;
        }

    </style>

</head>

<body class="is-preload">

    <!-- Header -->
    <header id="topheader">
        <p id="logo"><a href="#topheader"><img src="./images/logos/nox_logo_touka.png" class="headerimg"></a></p>
        <div class="navToggle">
            <span></span><span></span><span></span><span>menu</span>
        </div>
        <nav class="globalMenuSp">
            <ul>
                <li><a href="#">TOP</a></li>
                <li><a href="#about">ABOUT</a></li>
                <li><a href="#works">WORKS</a></li>
                <li><a href="#conpany">COMPANY</a></li>
                <li><a href="#access">ACCESS</a></li>
                <li><a href="#contact">CONTACT</a></li>
                <li><a href="recruit.php">募集ページへ</a></li>
            </ul>
        </nav>
    </header>
    <header id="header">
    </header>


    <!-- Banner -->
    <section id="about" class="banner">
        <header>
            <h2></h2>
            <h3>コンパニオン MC モデル募集</h3>
        </header>
        <p><br />
            ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入
            <br />ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入
        </p>

        <!--
				<footer>
					<a href="recruit.php" class="button style2 scrolly">コンパニオン MC モデル登録へ</a>
				</footer>
-->
    </section>

    <!-- Portfolio -->
    <article class="container box style2" id="works">
        <header>
            <h2>こんなイベント実績があります!</h2>
            <p>ここに説明を記入ここに説明を記入ここに説明を記入ここに説明を記入ここに説明を記入ここに説明を記入ここに説明を記入ここに説明を記入</p>
        </header>
        <div class="inner gallery">
            <div class="row gtr-0">
                <div class="col-3 col-12-mobile"><a href="images/fulls/01.jpg" class="image fit"><img src="images/thumbs/01.jpg" alt="" title="Ad infinitum" /></a>
                    <p class="captions">MODEL</p>
                </div>

                <div class="col-3 col-12-mobile"><a href="images/fulls/02_1.png" class="image fit"><img src="images/thumbs/02.jpg" alt="" title="Dressed in Clarity" /></a>
                    <p class="captions">コンパニオン</p>
                </div>
                <div class="col-3 col-12-mobile"><a href="images/fulls/03.jpg" class="image fit"><img src="images/thumbs/03.jpg" alt="" title="Raven" /></a>
                    <p class="captions">MC</p>
                </div>
            </div>

        </div>
    </article>

    <section id="banner2" class="banner">

        <header>
            <h2>イベント＆キャンペーンスタッフ大募集！</h2>
        </header>
        <p><br />
            ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入
            <br />ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入
        </p>

        <footer>
            <a href="recruit.php" class="button style2 scrolly">コンパニオン MC モデル登録へ</a>
        </footer>
    </section>
    <!-- Feature 1 -->
    <article id="first" class="container box style1 right">
        <a href="#" class="image fit"><img src="images/pic01.jpg" alt="" /></a>
        <div class="inner">
            <header>
                <h2>登録をお考えの皆様へーお仕事の流れ<br />
                </h2>
            </header>
            <p>ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入<br />
                ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入</p>
        </div>
    </article>

    <!-- Feature 2 -->
    <article class="container box style1 left">
        <a href="#" class="image fit"><img src="images/pic02.jpg" alt="" /></a>
        <div class="inner">
            <header>
                <h2>英語スタッフ派遣します！<br />
                </h2>
            </header>
            <p>ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入<br />
                ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入ここに文章を記入</p>
        </div>
    </article>

    <!--        company-->
    <article class="container box style3" id="conpany">
        <!--            <div class="inner">-->
        <header>
            <h2>COMPANY</h2>
        </header>
        <ul class="col2">

            <li>
                <dl>
                    <dt>社名</dt>
                    <dd>株式会社NO-X（ノックス）</dd>
                    <br>
                    <dt>設立</dt>
                    <dd>2004年10月</dd>
                    <br>
                    <dt>所在地</dt>
                    <dd>
                        <p>〒103-0002</p>
                        <p>東京都中央区日本橋馬喰町1-5-1馬喰町有楽ビル５F</p>
                    </dd>
                    <br>
                    <dt>TEL＆FAX</dt>
                    <dd>
                        <p>TEL：03-6264-8190</p>
                        <p>FAX：03-6264-8169</p>
                    </dd>
                    <br>
                    <dt>URL</dt>
                    <dd>https://no-x.co.jp</dd>
                    <br>
                    <dt>事業内容</dt>
                    <dd>
                        <p>イベント・展示会における、ナレーター・モデル・外国人モデル・レースクィーン・</p>
                        <p>コンパニオン・ キャンペーンガールの人材キャスティング・請負業</p>
                        <p>一般事務職・受付の人材派遣</p>
                        <p>翻訳関係の業務</p>
                        <p>英語スタッフの派遣</p>
                        <p>プロ通訳者の派遣</p>
                    </dd>
                    <br>
                    <dt>取引銀行</dt>
                    <dd>三井住友銀行　渋谷駅前支店<br>みずほ銀行　　 渋谷中央支店</dd>
                    <br>
                    <dt>取引先</dt>
                    <dd>
                        <p>鈴鹿サーキット</p>
                        <p>ツインリンクもてぎ</p>
                        <p>本田技研工業株式会社</p>
                        <p>株式会社ホンダモーターサイクルジャパン</p>
                        <p>株式会社博報堂</p>
                        <p>株式会社クオラス</p>
                        <p>岩正織物株式会社</p>
                        <p>株式会社セイノー情報サービス</p>
                        <p>株式会社仙台銘板</p>
                        <p>ミクロン精密株式会社</p>
                        <p>株式会社デザインスタジオ ドアーズ</p>
                        <p>株式会社リクルートコミュニケーションズ</p>
                        <p>ペトロナス</p>
                        <p>佐野学園</p>
                        <p>東洋石創</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <!--                    </div>-->
    </article>

    <article class="container box style3" id="access">
        <header>
            <h2>ACCESS</h2>
        </header>
        <!--            <div class="inner">-->
        <ul class="col2">
            <li>
                <!--
<p>〒103-0002<br>東京都中央区日本橋馬喰町1-5-1<br>馬喰町有楽ビル５F</p>
<p>TEL 03-6264-8190</p>
<p>FAX 03-6264-8169</p>
-->
                <!--
<p>e-Mail info@example.com</p>
<p>営業時間 10:00〜20:00（水曜定休）</p>
<p>※都合により休業する場合がございます</p>
-->
                <!--                        <br>-->
                <dl>
                    <dt>JR総武線</dt>
                    <dd>馬喰町駅出入口1より 徒歩1分</dd>
                    <br>
                    <dt>都営新宿線</dt>
                    <dd>馬喰横山駅A2出口より 徒歩2分</dd>
                    <br>
                    <dt>東京メトロ日比谷線</dt>
                    <dd>小伝馬町駅1番出口より 徒歩3分</dd>
                    <br>
                    <dt>都営浅草線</dt>
                    <dd>東日本橋駅A4出口より 徒歩4分</dd>
                    <br>
                    <dt>東京メトロ日比谷線／都営浅草線</dt>
                    <dd>人形町駅A4出口より 徒歩9分</dd>
                    <br>
                    <dt>都営新宿線</dt>
                    <dd>岩本町駅A5出口より 徒歩10分</dd>
                </dl>
            </li>

            <li>
                <div id="map">
                    <!-- GOOGLE MAP -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3240.3858272426933!2d139.77925451495858!3d35.6921219801918!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188952ccb5627b%3A0xdb672eb7084eb1bc!2z44CSMTAzLTAwMDIg5p2x5Lqs6YO95Lit5aSu5Yy65pel5pys5qmL6aas5Zaw55S677yR5LiB55uu77yVIOmmrOWWsOeUuuaciealveODk-ODqw!5e0!3m2!1sja!2sjp!4v1535111556818" width="100%" height="400" frameborder="0" style="border:0" allowfullscreen></iframe>
                    <!-- // GOOGLE MAP -->
                </div>
            </li>
        </ul>

        <!--            </div>-->
    </article>
    
    <!-- Contact -->
    <article class="container box style3" id="contact">
        <?php if( $page_flag === 1 ): ?>
        <h2>お問い合わせ内容</h2>
        <form method="post" action="">
            <div class="element_wrap">
                <label>氏名</label>
                <p>
                    <?php echo $clean['your_name']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>御社名</label>
                <p>
                    <?php echo $clean['your_company']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>メールアドレス</label>
                <p>
                    <?php echo $clean['email']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>電話番号</label>
                <p>
                    <?php echo $clean['tel']; ?>
                </p>
            </div>
            <div class="element_wrap">
                <label>お問い合わせ内容</label>
                <p>
                    <?php echo nl2br($clean['message']); ?>
                </p>
            </div>
            <input type="submit" name="btn_back" value="戻る">
            <input type="submit" name="btn_submit" onclick="location.href='./index.php#contact'" value="送信">
            <input type="hidden" name="your_name" value="<?php echo $clean['your_name']; ?>">
            <input type="hidden" name="your_company" value="<?php echo $clean['your_company']; ?>">
            <input type="hidden" name="email" value="<?php echo $clean['email']; ?>">
            <input type="hidden" name="tel" value="<?php echo $clean['tel']; ?>">
            <input type="hidden" name="message" value="<?php echo $clean['message']; ?>">
        </form>
        <?php elseif( $page_flag === 2 ): ?>

        <p>送信が完了しました。</p>
        <p><a href="index.php">トップに戻る</a></p>

        <?php else: ?>

        <header>
            <h2>お問い合わせ</h2>

            <?php if( !empty($error) ): ?>
            <ul class="error_list">
                <?php foreach( $error as $value ): ?>
                <li>
                    <?php echo $value; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </header>
        <form method="post" action="" enctype="multipart/form-data">
            <p>*は必須項目です</p>
            <div class="row gtr-50">
                <div class="col-6 col-12-mobile"><input type="text" class="text" name="your_name" placeholder="*Name" value="<?php if( !empty($clean['your_name']) ){ echo $clean['your_name']; } ?>" /></div>

                <div class="col-6 col-12-mobile"><input type="text" class="text" name="your_company" placeholder="Company" value="<?php if( !empty($clean['your_company']) ){ echo $clean['your_company']; } ?>" /></div>

                <div class="col-6 col-12-mobile"><input type="text" class="text" name="email" placeholder="*Email" value="<?php if( !empty($clean['email']) ){ echo $clean['email']; } ?>" /></div>

                <div class="col-6 col-12-mobile"><input type="number" pattern="\d*" class="text" name="tel" placeholder="Tel(ハイフンなし)" value="<?php if( !empty($clean['tel']) ){ echo $clean['tel']; } ?>" /></div>


                <div class="col-12">
                    <textarea name="message" placeholder="*Message"><?php if( !empty($clean['message']) ){ echo $clean['message']; } ?></textarea>
                </div>
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
                    株式会社　ノックス<br>
                    TEL 03-6264-8190
                </div>
                <div class="col-12">
                    <ul class="actions">
                        <li><input type="submit" name="btn_confirm" onclick="location.href='#contact'" value="入力内容を確認する" /></li>
                    </ul>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </article>

    <!--
<li><a href="#" class="icon fa-twitter"><span class="label">Twitter</span></a></li>
<li><a href="#" class="icon fa-facebook"><span class="label">Facebook</span></a></li>
<li><a href="#" class="icon fa-google-plus"><span class="label">Google+</span></a></li>
<li><a href="#" class="icon fa-pinterest"><span class="label">Pinterest</span></a></li>
<li><a href="#" class="icon fa-dribbble"><span class="label">Dribbble</span></a></li>
<li><a href="#" class="icon fa-linkedin"><span class="label">LinkedIn</span></a></li>
-->


    <section id="footer">
        <article class="container box style2">
            <p>関連会社</p>
            <div class="icons">
                <a href="http://c4planning.co.jp"><img src="images/logos/c4_logo_long.png" alt="" class="c4_img" /></a>
            </div>
        </article>
        <div class="copyright">
            <ul class="menu">
                <li>&copy; NO-X. All rights reserved.</li>
                <li class="hide">Design: <a href="http://html5up.net">HTML5 UP</a></li>
            </ul>
        </div>


    </section>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.poptrox.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>
