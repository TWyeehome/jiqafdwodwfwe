<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("./components/meta.php"); ?>
    <style>
        .main_title {
            font-size: 26px;
            font-weight: 500;
            line-height: 38px;
            letter-spacing: 0;
            text-align: center;
        }

        .sub_title {
            font-size: 12px;
            font-weight: 500;
            line-height: 17px;
            letter-spacing: 0;
            text-align: center;
        }
    </style>
</head>

<body class="--TrackModeActiveoff --TrackTagActiveoff --EFmainheaderShowoff --EFmenuBtnShowoff">
    <!-- fade -->
    <span class="popup_fade"></span>
    <main id="vueApp" class="Layout --KIRE__wrapper">
        <section id="mainpage">
            <article class="px-0 Layout__MobileArea Layout__container" style="float: none; margin: auto;">
                <div class="Mainheader --fixed bg-white" style="left: 0; right: 0;">
                    <div class="Mainheader__logo">
                        <div class="Mainheader__logo__txt">
                            AI靈感大師:澳洲3D光影觸動樂園<br>
                            MODERN GURU AND THE PATH TO ARTIFICIAL HAPPINESS
                        </div>
                    </div>
                    <div class="Mainheader__menubtn">
                        <div class="Mainheader__menubtn__box">
                            <nav class="Mainheader__menubtn__box__menu">
                                <ul>
                                    <li>LOGIN</li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <main>
                    <?php require_once("./components/header.php"); ?>
                    <section v-if="filled == true">
                        <article class="convert_conitaner border-top">
                            <h2 class="main_title" style="margin-top: 32px;">《AI靈感大師：澳洲3D光影觸動樂園》</h2>
                            <p class="sub_title d-flex justify-content-center">
                                我要購票！
                                <a class="text-black" href="./index.php">購票去</a>
                            </p>
                            <div class="qr_box">
                                <p v-html="info"></p>
                            </div>
                            <a class="convert_download" style="font-size: 16px;" href="https://meet.eslite.com/Content/DM/MG2_20230919185717.pdf" download="展覽手冊">
                                展覽手冊下載
                            </a>
                        </article>
                        <hr>
                        <img src="./img/buy_rules.png?20230828" alt="buy_rules" loading="lazy" class="w-100 px-3" style="margin: 32px 0;">
                    </section>
                    <section v-if="filled == false">
                        <article class="convert_conitaner border-top">
                            <h2 class="main_title" style="margin-top: 32px;">歡迎蒞臨《AI靈感大師：澳洲3D光影觸動樂園》</h2>
                            <p class="sub_title">請選擇您的購票管道。</p>
                            <!-- radio -->
                            <input class="d-none" type="radio" name="way" id="way_1" checked>
                            <label class="convert_btn" for="way_1">
                                <h3 style="padding-bottom: 4px;">誠品官方售票平台</h3>
                                <p>Powered by AMPING</p>
                            </label>
                            <input class="d-none" type="radio" name="way" id="way_2">
                            <label class="convert_btn" for="way_2">
                                <h3>udn售票網</h3>
                            </label>
                            <input class="d-none" type="radio" name="way" id="way_4">
                            <label class="convert_btn" for="way_4">
                                <h3>ibon售票系統</h3>
                            </label>
                            <input class="d-none" type="radio" name="way" id="way_3">
                            <label class="convert_btn mb-0" for="way_3">
                                <h3>Klook</h3>
                            </label>
                        </article>
                        <hr>
                        <article class="convert_conitaner">
                            <h2 class="main_title" style="margin-top: 60px; margin-bottom: 48px;">兌換展覽電子手冊</h2>
                            <input placeholder="請輸入Email或手機號碼" class="buy_coupon mb-2" v-model="contact">
                            <button class="buy_btn buy_btn-buy d-block mx-auto" style="margin-bottom: 120px; height: 56px;" @click="send();">立即兌換</button>
                        </article>
                    </section>
                    <?php require_once("./components/footer.php"); ?>
                </main>
            </article>
            <!-- cookie -->
            <article class="ticket_model">
                <h3>本網站使用事項</h3>
                <p>本網站使用cookie，繼續使用即代表您同意我們使用cookie為您帶來更好的體驗，並在使用過程中幫助您註冊為用戶。購買本站任何票券視同同意相關購買條款。</p>
                <a href="./policy.php" target="_blank">更多詳情</a>
                <button id="model_hide">我同意，並繼續瀏覽本網站</button>
            </article>
        </section>
    </main>
    <?php require_once("./components/source.php"); ?>
</body>

</html>