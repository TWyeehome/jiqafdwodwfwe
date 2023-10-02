<?php
if (empty(@$_COOKIE['member_code'])) {
    header("Location: ./index.php");
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("./components/meta.php"); ?>
</head>

<body class="--TrackModeActiveoff --TrackTagActiveoff --EFmainheaderShowoff --EFmenuBtnShowoff" style="background-image: url(./img/cover.png?20230816);">
    <!-- fade -->
    <span class="popup_fade d-none"></span>
    <main id="vueApp" class="Layout --KIRE__wrapper">
        <section id="mainpage">
            <div class="px-0 Layout__MobileArea Layout__container" style="float: none; margin: auto;">
                <div class="Mainheader --fixed bg-white" style="left: 0; right: 0; z-index: 999999999999999;">
                    <div class="Mainheader__logo">
                        <div class="Mainheader__logo__txt">AI靈感大師:澳洲3D光影觸動樂園<br>MODERN GURU AND THE PATH TO ARTIFICIAL HAPPINESS</div>
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
                    <!-- 會員 -->
                    <div class="dropdown_container" style="display: none;">
                        <div class="dropdown_item">
                            <p>我的帳戶</p>
                            <img src="./img/head.png" alt="head" width="14" height="14">
                        </div>
                        <div class="dropdown_item logout">
                            <p>登出</p>
                            <img src="./img/logout.png" alt="logout" width="14" height="14">
                        </div>
                    </div>
                    <?php require_once("./components/header.php"); ?>
                    <!-- 動態視窗加入 --active 開啟 -->
                    <section class="qr_container" style="border-bottom: 0;">
                        <article class="qr_modal">
                            <h2 class="qr_title">AI靈感大師:澳洲3D光影觸動樂園</h2>
                            <div id="ticket_detail" class="d-flex justify-content-center mb-3">
                                <span class="qr_result" v-html="ticketStatus"></span>
                                <span id="ticket_gift" class="qr_result qr_result-gift ms-2" data-gift="">贈品未兌換</span>
                            </div>
                            <!--<span class="qr_status" style="margin-bottom: 16px;">{{ dayOff }}</span>-->
                            <div class="qr_box" style="background-image: url(./img/white.jpg);">
                                <a href="./index.php" class="d-block" style="color: #0D0D10;">購票去</a>
                            </div>
                            <article class="d-flex justify-content-center" style="margin: 16px auto 4px auto;">
                                <div class="position-relative">
                                    <select class="qr_status qr_status-select m-0 me-1" v-model="ticketFilter" @change="ticketIndex = 0; qrcode(0);">
                                        <option value="all">所有票種</option>
                                        <!--<option value="ticket_test">測試票</option>-->
                                        <option value="ticket_discount">超級早鳥票</option>
                                        <option value="ticket_early">早鳥票</option>
                                        <option value="ticket_normal">全票</option>
                                        <option value="ticket_family">親子套票</option>
                                    </select>
                                    <img class="position-absolute" style="right: 8px; top: 3.5px; pointer-events: none;" src="./img/arrow-down.png" alt="arrow" width="12" height="12">
                                </div>
                                <span class="qr_status m-0" v-html="ticketType + ' #' + ticketName"></span>
                            </article>
                            <div class="d-flex justify-content-center align-items-center">
                                <img id="previous" class="qr_next" src="./img/left.png" alt="left" width="36" height="36">
                                <p class="qr_item">
                                    <span v-if="ticketIndex + 1 < 10">0</span><span v-if="getLeft > 0">{{ ticketIndex + 1 }}</span><span v-else>0</span>
                                    /
                                    <span v-if="getLeft < 10">0</span>{{ getLeft }}
                                </p>
                                <img id="next" class="qr_next" src="./img/right.png" alt="right" width="36" height="36">
                            </div>
                            <!-- 展覽手冊下載 -->
                            <p id="manual" class="ticket_content d-none mt-3 p-0">
                                <a href="https://meet.eslite.com/Content/DM/MG2_20230919185717.pdf" download="展覽手冊">展覽手冊下載</a>
                            </p>
                            <p class="ticket_content w-75 mx-auto mt-3 p-0">於本服務購買的為數位票券，入場前出示此QRCODE掃描後即可入場。<br><br>
                                如需分票，可截圖各自單一QRCODE即可。</p>
                        </article>
                    </section>
                    <!-- 票券資訊 -->
                    <section class="ticket_box" style="border-bottom: 0;">
                        <h2 class="ticket_title">票券資訊</h2>
                        <p class="ticket_content d-flex p-0" style="margin-bottom: 48px;">*還想再購買幾張嗎？<a href="./index.php">購票去</a></p>
                        <!-- 目前持有 -->
                        <article class="ticket_stub">
                            <header class="ticket_header">
                                <div class="d-flex">
                                    <p class="ticket_total ticket_total-highlight m-0" v-if="ticketTotal < 10">0</p>
                                    <p class="ticket_total ticket_total-highlight" v-html="ticketTotal">5</p>
                                    <p class="ticket_total ms-2">張購票</p>
                                </div>
                            </header>
                            <section class="ticket_result justify-content-center">
                                <div class="d-flex">
                                    <div class="ticket_status">成功購票</div>
                                    <img class="ticket_receipt ms-1" src="./img/receipt-text.png" alt="receipt" data-invoice="all">
                                </div>
                            </section>
                        </article>
                    </section>
                    <section class="ticket_box">
                        <h2 class="ticket_title">我的票匣</h2>
                        <!-- 測試票 -->
                        <!--<article class="ticket_stub">
                            <section class="ticket_result">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <p class="ticket_total ticket_total-item ms-2">測試票</p>
                                    <div class="d-flex" v-if="ticketTestUsed != 0">
                                        <p class="ticket_total ticket_total-highlight m-0">已兌換 {{ ticketTestUsed }} 張 尚未兌換 {{ ticketTestUnused }} 張</p>
                                    </div>
                                    <div class="d-flex" v-if="ticketTestUsed == 0">
                                        <p class="ticket_total ticket_total-highlight m-0" v-if="ticketTestUnused < 10">0</p>
                                        <p class="ticket_total ticket_total-highlight m-0" v-html="ticketTestUnused">0</p>
                                        <p class="ticket_total ms-2">張購票</p>
                                    </div>
                                    <img class="ticket_receipt" src="./img/receipt-text.png" alt="receipt" data-receipt="ticket_test">
                                </div>
                            </section>
                        </article>-->
                        <!-- 超級早鳥票 -->
                        <article class="ticket_stub">
                            <section class="ticket_result">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <p class="ticket_total ticket_total-item ms-2">超級早鳥票</p>
                                    <div class="d-flex" v-if="ticketDiscountUsed != 0">
                                        <p class="ticket_total ticket_total-highlight m-0">已兌換 {{ ticketDiscountUsed }} 張 尚未兌換 {{ ticketDiscountUnused }} 張</p>
                                    </div>
                                    <div class="d-flex" v-if="ticketDiscountUsed == 0">
                                        <p class="ticket_total ticket_total-highlight m-0" v-if="ticketDiscountUnused < 10">0</p>
                                        <p class="ticket_total ticket_total-highlight m-0" v-html="ticketDiscountUnused">0</p>
                                        <p class="ticket_total ms-2">張購票</p>
                                    </div>
                                    <img class="ticket_receipt" src="./img/receipt-text.png" alt="receipt" data-receipt="ticket_discount">
                                </div>
                            </section>
                        </article>
                        <!-- 早鳥票 -->
                        <article class="ticket_stub">
                            <section class="ticket_result">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <p class="ticket_total ticket_total-item ms-2">早鳥票</p>
                                    <div class="d-flex" v-if="ticketEarlyUsed != 0">
                                        <p class="ticket_total ticket_total-highlight m-0">已兌換 {{ ticketEarlyUsed }} 張 尚未兌換 {{ ticketEarlyUnused }} 張</p>
                                    </div>
                                    <div class="d-flex" v-if="ticketEarlyUsed == 0">
                                        <p class="ticket_total ticket_total-highlight m-0" v-if="ticketEarlyUnused < 10">0</p>
                                        <p class="ticket_total ticket_total-highlight m-0" v-html="ticketEarlyUnused">5</p>
                                        <p class="ticket_total ms-2">張購票</p>
                                    </div>
                                    <img class="ticket_receipt" src="./img/receipt-text.png" alt="receipt" data-receipt="ticket_early">
                                </div>
                            </section>
                        </article>
                        <!-- 全票 -->
                        <article class="ticket_stub">
                            <section class="ticket_result">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <p class="ticket_total ticket_total-item ms-2">全票</p>
                                    <div class="d-flex" v-if="ticketNormalUsed != 0">
                                        <p class="ticket_total ticket_total-highlight m-0">已兌換 {{ ticketNormalUsed }} 張 尚未兌換 {{ ticketNormalUnused }} 張</p>
                                    </div>
                                    <div class="d-flex" v-if="ticketNormalUsed == 0">
                                        <p class="ticket_total ticket_total-highlight m-0" v-if="ticketNormalUnused < 10">0</p>
                                        <p class="ticket_total ticket_total-highlight m-0" v-html="ticketNormalUnused">0</p>
                                        <p class="ticket_total ms-2">張購票</p>
                                    </div>
                                    <img class="ticket_receipt" src="./img/receipt-text.png" alt="receipt" data-receipt="ticket_normal">
                                </div>
                            </section>
                        </article>
                        <!-- 親子套票 -->
                        <article class="ticket_stub">
                            <section class="ticket_result">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <p class="ticket_total ticket_total-item ms-2">親子套票</p>
                                    <div class="d-flex" v-if="ticketFamilyUsed != 0">
                                        <p class="ticket_total ticket_total-highlight m-0">已兌換 {{ ticketFamilyUsed }} 張 尚未兌換 {{ ticketFamilyUnused }} 張</p>
                                    </div>
                                    <div class="d-flex" v-if="ticketFamilyUsed == 0">
                                        <p class="ticket_total ticket_total-highlight m-0" v-if="ticketFamilyUnused < 10">0</p>
                                        <p class="ticket_total ticket_total-highlight m-0" v-html="ticketFamilyUnused">0</p>
                                        <p class="ticket_total ms-2">張購票</p>
                                    </div>
                                    <img class="ticket_receipt" src="./img/receipt-text.png" alt="receipt" data-receipt="ticket_family">
                                </div>
                            </section>
                        </article>
                        <p class="ticket_content text-center w-75 mx-auto pt-3">
                            <a class="mb-3" href="https://shareablecorp.notion.site/Modern-Guru-Q-A-24cbc83992694848884b7e05647df0fc">售票 Q&A</a>
                            依據文化部文化藝術事業減免營業稅及娛樂稅辦法，本活動已認可為營業稅免徵，詳見文藝字第 1123023301 號之許可說明。
                        </p>
                    </section>
                    <!---->
                    <img class="w-100 px-3" src="./img/buy_rules.png?20230828" alt="buy_rules" loading="lazy">
                    <!-- 退票申請 -->
                    <section class="ticket_box">
                        <h2 class="ticket_title">退票申請</h2>
                        <p class="ticket_content">退票作業說明</p>
                        <p class="ticket_content">為維護當事人個資安全，本退票服務僅限當事人申請，填寫退票作業說明：</p>
                        <p class="ticket_content">為維護當事人個資安全,本退票服務僅限當事人申請,填寫退票作業說明:退票申請並須檢附「申請人姓名」、「申請人聯絡電話」、「訂單編號」、「信用卡資訊」等完整資料(以下統稱退票作業資料),以提出退票(款)申請作業;一旦AMPING(劉氏股份有限公司)(以下統稱本公司)收訖退票作業資料,視同您同意本公司進行相關退票作業,包含電話聯繫、缺(補)件作業等。</p>
                        <p class="ticket_content">退票款項將扣除相關手續費用(折扣後的票價金額百分之十)後,於原刷卡購票之刷卡銀行帳單退回,請於次月於帳單明細中查詢或主動向銀行端查詢退款作業。退款流程需約20個工作天(收到退票申請起計算),颱風、疫情或政府命令因此閉門或延期演出之節目,退票作業時間不在此限。</p>
                        <p class="ticket_content">補件或者欲更改開立之統一編號與公司抬頭:請拍照或掃瞄方式將資料以電子檔附件方式MAIL至 hello@amping.io 到AMPING退票服務中心,主旨請敘述欲更改或退票之活動及補件內容(若跨雙月將無法辦理統一編號之更改)。</p>
                        <p class="ticket_content ">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeOoa9bppnbUqxSHMR2cxg9jS1_A7I4VGFwPTKVNxXwyN3bxQ/viewform" style="color: rgba(0, 0, 255, 1);">前往申請</a>
                        </p>
                    </section>
                    <?php require_once("./components/footer.php"); ?>
                </main>
            </div>
            <!-- buy -->
            <article class="buy">
                <section class="buy_box buy_box-check">
                    <div class="event buy_btn buy_btn-logo p-1" @click="location.href = './index.php';" style="background-image: url(./img/logo.png);"></div>
                    <button class="event buy_btn buy_btn-buy" @click="location.href = './index.php';">返回活動頁面</button>
                </section>
            </article>
            <!-- ticket_model -->
            <section class="model_receipt ticket_model ticket_model-receipt d-none">
                <h2 class="ticket_open ticket_title text-center p-0" style="margin-top: 16px; margin-bottom: 2px;">票根明細</h2>
                <p class="ticket_open ticket_content p-0" style="margin-bottom: 16px;">相關購票明細顯示於此</p>
                <article id="receipt" class="ticket_open"></article>
            </section>
            <!-- 贈品 -->
            <article id="modal_gift" class="modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">贈品兌換</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-0">
                            <p class="my-3">請出示此畫面讓工作人員點擊「立即兌換」按鈕。</p>
                            <button id="gift_exchange" class="buy_btn buy_btn-buy d-block mx-auto">立即兌換</button>
                            <p class="my-3">注意事項：請勿自行點擊按鈕，如無故點擊按鈕則視同已兌換；請於展覽期間內完成兌換，逾時不候。</p>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </main>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <?php require_once("./components/source.php"); ?>
</body>

</html>