<?php

// Translation: Alperen Yusuf Aybar (http://alperen.aybar.biz) (alperen@aybar.biz)
// Revision: $Revision$
// EN-Revision: 1.11

$error[0] = 'Geçersiz URL: <em>%s</em>!';
$error[1] = "<em>%s</em> konumuna baglantı sağlanamadı!";
$error[2] = 'Girdi yok!';
$error[3] = "Karakter kodlaması otomatik olarak bulunamadı. Varsayılan kodlama kullanılıyor.";
$error[4] = "Düzenli bir kaynak yok. İlk önce parse_string() 'i veya parse_url() 'yi çağırın!";
$error[5] = "Otomatik olarak bir karakter kodlaması buldum (&quot;<em>%s</em>&quot;), fakat bunu tanımlayamadım.";
$error[6] = 'Belirttiğiniz sayfa çok büyük (Sınır: %s byte). Sayfanın tümü incelenemeyecek!';
$error[7] = 'Bağlantı zaman aşımına uğradı.';
$error[8] = 'Dosya çok büyük. Sınır %s byte';
$error[9] = 'Dosya gönderimi başarıyla tamamlanamadı.';
$error[10]= '"%s" dili için aspell kütüphanesi başlatılamadı, bu yüzden yazım yanlışları kontrol edilmedi.';


/* other messages */
$message[0]  = 'Seçenekleri yapılandır';
$message[1]  = 'Hataların biçimi';
$message[2]  = 'Onarılan HTML/XML kodu nasıl görüntülenecek';
$message[3]  = 'Onaylanacak URL';
$message[4]  = 'Onayla';
$message[5]  = 'Belgeleme';
$message[6]  = 'Kaynak ile beraber';
$message[7]  = 'Yalnız';
$message[8]  = 'Hataları göster';
$message[9]  = 'Onarılan HTML/XML kodunu göster';
$message[10] = 'Tam';
$message[11] = 'Fark';
$message[12] = 'Hiçbiri';
$message[13] = 'Bir hata oluştu.';
$message[14] = 'Bulunan sürüm';
$message[15] = 'Hiçbir hata bulunamadı!';
$message[16] = 'Hatalar';
$message[17] = 'Onarılan HTML/XML kodu';
$message[18] = 'Dosya';
$message[19] = 'Dosya gönder';
$message[20] = 'Kodlama kullanımda';
$message[21] = 'Hatalar bulundu';
$message[22] = 'HTML/XML Onaylayıcı';
$message[23] = '"%s" sözcüğü büyük ihtimalle yanlış yazılmış. öneriler: %s';
$message[24] = 'Dil';
$message[25] = 'hayır';
$message[99] = 'Bu HTML/XHTML/XML onaylayıcıda <a href="http://tidy.sf.net">Tidy</a> ve <a href="http://www.php.net">PHP 5</a> temel alınmıştır. Sizin özdevimli olarak HTML, XHTML ve XML kodlarını onaylamanızı ve onarmanızı sağlar. Ayrıca HTML belgelerini tek bir tıkla XHTML\'ye çevirmenizi sağlar.</p><p>Unutmayın ki bu yazılım ve Tidy yazılımı hala geliştirme aşamasındadır. Eğer bir hata bulursanız veya herhangi bir sorunuz olursa, lütfen <a href="http://sourceforge.net/tracker/?group_id=143995">fikir meydanımızı</a> kullanın.';


/* Name for each option */
$opts['accessibility-check']['name'] = 'Erişilebilirlik düzeyini denetle';
$opts['alt-text']['name']            = 'Alt Metni';
$opts['charset']['name']             = 'Karakter Kodlaması';
$opts['hide-comments']['name']       = 'Yorumları çıkar';
$opts['indent']['name']              = 'Paragraf';
$opts['indent-spaces']['name']       = 'Paragraf boşluğu';
$opts['output']['name']              = 'Girdi/Çıktı Biçimi';
$opts['spellcheck']['name']          = 'Yazım Kontrolü';
$opts['wrap']['name']                = 'Kaydırma';


/* description for each option */
$opts['accessibility-check']['explain'] = 'Erişilebilirlik onaylayıcısı için öncelik seviyesini seçin, geçersiz kılmak için 0 değerini seçin. Daha fazla bilgi için: <a href="http://www.w3.org/WAI/">http://www.w3.org/WAI/</a>';
$opts['alt-text']['explain']            = 'Bazı resimlerde kayıp olan alt özniteliği yerine geçecek bir metin yazin. Daha sonra bu metni resmin açıklamasıyla değiştirmelisiniz!';
$opts['charset']['explain']             = 'Belgenin yorumlanacağı karakter kodlamasını seçin. Kodlamanın kendiliğinden bulunması için "auto" olarak bırakın';
$opts['hide-comments']['explain']       = 'Belgedeki yorumları kaldırmak istiyorsanız bu şıkkı seçiniz.';
$opts['indent']['explain']              = 'Metninizin otomatik olarak yeni satırlara geçmesini istiyorsanız bu şıkkı seçiniz.';
$opts['indent-spaces']['explain']       = 'Paragraf boşluğu. Etkin hale gelmesi için paragraf özelliğini seçili hale getirmeniz gerek.';
$opts['output']['explain']              = "Girdi/Çıktı biçimini seçiniz. Eğer bir HTML belgesini XHTML'ye çevirmek istiyorsaniz XHTML özelliğini seçmelisiniz.";
$opts['spellcheck']['explain']          = "Yazım kontrolü yapmak için bir dil seçiniz. Bu özelliği devre dışı bırakmak içim 'hayır' seçeneğini seçiniz. Lütfen aynı satırdaki tekrarlanan yanlışların rapor edilmeyeceğini unutmayın.";
$opts['wrap']['explain']                = 'Satırları girilen satır uzunluğunda kaydırır, eğer bu özelliği kullanmak istemiyorsanız değer olarak 0 giriniz.';

/* languages names */
$langs['en_CA'] = 'İngilizce (Kanada)';
$langs['en_GB'] = 'İngilizce (Britanya)';
$langs['en_US'] = 'İngilizce (Amerika)';
$langs['it']    = 'İtalyanca';
$langs['nb']    = 'Norveççe (Bokmal)';
$langs['nl']    = 'Hollandaca';
$langs['pl']    = 'Polonyali';
$langs['pt']    = 'Portekizce';
$langs['sv']    = 'İsveççe';
$langs['tr']    = 'Türkçe';
?>
