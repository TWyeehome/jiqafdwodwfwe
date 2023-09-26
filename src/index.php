<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("./components/meta.php"); ?>
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
                            AI靈感大師:澳洲3D光影觸動樂園
                            <br>
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
                    <!-- 動態視窗加入 --active 開啟 -->
                    <section class="ticket_box border-0 p-0">
                        <img class="w-100" src="./img/info.png?678687" alt="eness">
                    </section>
                    <?php require_once("./components/footer.php"); ?>
                </main>
            </article>
            <!-- buy -->
            <article class="buy">
                <article class="buy_ticket" :class="{ 'd-none': !goBuy }">
                    <p class="buy_title">購票</p>
                    <p class="buy_subtitle">購票請先使用手機或 Email 登入。</p>
                    <p class="buy_subtitle" v-if="ticketDate == ''">
                        超級早鳥票尚未開賣<br>
                        開賣時間<br>
                        2023/08/31 12PM<br>
                        僅限誠品會員<br>
                        敬請期待!
                    </p>
                    <p class="buy_subtitle" style="color: #4949EE;" v-if="ticketDate == 'a'">
                        <a href="./profile.php" style="color: #0d6efd;">※ 欲購【超級早鳥票】請先至"我的帳戶"填寫誠品會員卡號</a>
                    </p>
                    <!-- 測試票 -->
                    <!--<section class="Mainvision__loop__loop" style="pointer-events: auto;" v-if="ticketDate == 'n'">
                        <div class="loop JS_LOOP">
                            <div class="loop__container">
                                <h3 class="loop__title align-items-center">
                                    <div class="ticket_buy" @click="ticketTest -= 1; if (ticketTest < 0 ) { ticketTest = 0; }; document.cookie = 'ticketTest=' + ticketTest;">
                                        <img src="./img/minus-cirlce.png" alt="minus" width="30" height="30">
                                    </div>
                                    <div class="ticket_buy" @click="ticketTest += 1; if (ticketTest > 99 ) { ticketTest = 99; }; document.cookie = 'ticketTest=' + ticketTest;">
                                        <img src="./img/add-circle.png" alt="add" width="30" height="30">
                                    </div>
                                    <div class="ticket_item">測試票</div>
                                    <div class="ticket_item ticket_item-price">20 x</div>
                                    <div class="ticket_number">
                                        <div class="ticket_counts" v-if="ticketTest < 10">0</div>
                                        <div class="ticket_counts" v-html="ticketTest">1</div>
                                    </div>
                                </h3>
                            </div>
                        </div>
                    </section>-->
                    <!-- 超級早鳥票 -->
                    <!--<section class="Mainvision__loop__loop" style="pointer-events: auto;" v-if="esliteCode != '' && ticketDate == 'a'">
                        <div class="loop JS_LOOP">
                            <div class="loop__container">
                                <h3 class="loop__title align-items-center">
                                    <div class="ticket_buy" @click="ticketDiscount -= 1; if (ticketDiscount < 0 ) { ticketDiscount = 0; }; document.cookie = 'ticketDiscount=' + ticketDiscount;">
                                        <img src="./img/minus-cirlce.png" alt="minus" width="30" height="30">
                                    </div>
                                    <div class="ticket_buy" @click="ticketDiscount += 1; if (ticketDiscount > 99 ) { ticketDiscount = 99; }; document.cookie = 'ticketDiscount=' + ticketDiscount;">
                                        <img src="./img/add-circle.png" alt="add" width="30" height="30">
                                    </div>
                                    <div class="ticket_item">超級早鳥票</div>
                                    <div class="ticket_item ticket_item-price">300 x</div>
                                    <div class="ticket_number">
                                        <div class="ticket_counts" v-if="ticketDiscount < 10">0</div>
                                        <div class="ticket_counts" v-html="ticketDiscount">1</div>
                                    </div>
                                </h3>
                            </div>
                        </div>
                    </section>-->
                    <!-- 早鳥票 -->
                    <!--<section class="Mainvision__loop__loop" style="pointer-events: auto;" v-if="ticketDate == 'b'">
                        <div class="loop JS_LOOP">
                            <div class="loop__container">
                                <h3 class="loop__title align-items-center">
                                    <div class="ticket_buy" @click="ticketEarly -= 1; if (ticketEarly < 0 ) { ticketEarly = 0; }; document.cookie = 'ticketEarly=' + ticketEarly;">
                                        <img src="./img/minus-cirlce.png" alt="minus" width="30" height="30">
                                    </div>
                                    <div class="ticket_buy" @click="ticketEarly += 1; if (ticketEarly > 99 ) { ticketEarly = 99; }; document.cookie = 'ticketEarly=' + ticketEarly;">
                                        <img src="./img/add-circle.png" alt="add" width="30" height="30">
                                    </div>
                                    <div class="ticket_item">早鳥票</div>
                                    <div class="ticket_item ticket_item-price">300 x</div>
                                    <div class="ticket_number">
                                        <div class="ticket_counts" v-if="ticketEarly < 10">0</div>
                                        <div class="ticket_counts" v-html="ticketEarly">1</div>
                                    </div>
                                </h3>
                            </div>
                        </div>
                    </section>-->
                    <!-- 全票 -->
                    <section class="Mainvision__loop__loop border-bottom-0" style="pointer-events: auto;" v-if="ticketDate == 'c'">
                        <div class="loop JS_LOOP">
                            <div class="loop__container">
                                <h3 class="loop__title align-items-center">
                                    <div class="ticket_buy" @click="ticketNormal -= 1; if (ticketNormal < 0 ) { ticketNormal = 0; }; document.cookie = 'ticketNormal=' + ticketNormal;">
                                        <img src="./img/minus-cirlce.png" alt="minus" width="30" height="30">
                                    </div>
                                    <div class="ticket_buy" @click="ticketNormal += 1; if (ticketNormal > 99 ) { ticketNormal = 99; }; document.cookie = 'ticketNormal=' + ticketNormal;">
                                        <img src="./img/add-circle.png" alt="add" width="30" height="30">
                                    </div>
                                    <div class="ticket_item">全票</div>
                                    <div class="ticket_item ticket_item-price">360 x</div>
                                    <div class="ticket_number">
                                        <div class="ticket_counts" v-if="ticketNormal < 10">0</div>
                                        <div class="ticket_counts" v-html="ticketNormal">1</div>
                                    </div>
                                </h3>
                            </div>
                        </div>
                    </section>
                    <!-- 親子套票 -->
                    <section class="Mainvision__loop__loop" style="pointer-events: auto;" v-if="ticketDate == 'c'">
                        <div class="loop JS_LOOP">
                            <div class="loop__container">
                                <h3 class="loop__title align-items-center">
                                    <div class="ticket_buy" @click="ticketFamily -= 1; if (ticketFamily < 0 ) { ticketFamily = 0; }; document.cookie = 'ticketFamily=' + ticketFamily;">
                                        <img src="./img/minus-cirlce.png" alt="minus" width="30" height="30">
                                    </div>
                                    <div class="ticket_buy" @click="ticketFamily += 1; if (ticketFamily > 99 ) { ticketFamily = 99; }; document.cookie = 'ticketFamily=' + ticketFamily;">
                                        <img src="./img/add-circle.png" alt="add" width="30" height="30">
                                    </div>
                                    <div class="ticket_item">親子套票</div>
                                    <div class="ticket_item ticket_item-price">600 x</div>
                                    <div class="ticket_number">
                                        <div class="ticket_counts" v-if="ticketFamily < 10">0</div>
                                        <div class="ticket_counts" v-html="ticketFamily">1</div>
                                    </div>
                                </h3>
                            </div>
                        </div>
                    </section>
                    <!-- 折扣碼 -->
                    <!--<input class="buy_coupon mb-0" type="text" placeholder="折扣碼" v-model="coupon" @input="couponCheck('input');">-->
                    <!--<div class="form-check d-flex justify-content-center my-3">
                        <input id="company_pay" class="form-check-input" type="checkbox" :value="companyPay" v-model="companyPay">
                        <label for="company_pay" class="form-check-label ms-1" style="color: rgba(0,0,0,.3);">是否需要開立統一編號?</label>
                    </div>-->
                    <br>
                    <input class="buy_coupon border-bottom-0 mb-0" type="text" placeholder="公司抬頭" v-model="companyName" v-if="companyPay">
                    <input class="buy_coupon" type="text" placeholder="統一編號" v-model="companyId" v-if="companyPay">
                    <div class="d-flex justify-content-between">
                        <p class="buy_total">加總 NTD$</p>
                        <p class="buy_price">
                            {{
                                farmat(
                                    Math.ceil((20 * couponPrice)) * ticketTest + 
                                    300 * ticketDiscount + 
                                    300 * ticketEarly + 
                                    Math.ceil((360 * couponPrice)) * ticketNormal + 
                                    600 * ticketFamily
                                ) 
                            }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-between" v-if="ticketDate == 'c'">
                        <p class="buy_total" v-html="couponName"></p>
                        <p class="buy_price" v-html="couponStatus"></p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p class="buy_total">總金額 NTD$</p>
                        <p class="buy_price">
                            {{
                                farmat(
                                    Math.ceil((20 * couponPrice)) * ticketTest + 
                                    300 * ticketEarly + 
                                    300 * ticketDiscount + 
                                    Math.ceil((360 * couponPrice)) * ticketNormal + 
                                    600 * ticketFamily
                                ) 
                            }}
                        </p>
                    </div>
                    <hr>
                    <section>
                        <img class="w-100" src="./img/buy_rules.png?20230828" alt="buy_rules" loading="lazy">
                    </section>
                </article>
                <article class="buy_box" :class="{ 'd-none': goBuy }">
                    <div class="buy_btn buy_btn-logo" @click="location.reload();" style="background-image: url(./img/logo.png);"></div>
                    <button class="buy_btn buy_btn-buy" v-if="login == true && couponUsable == false" @click="saleDate(); goBuy = true;">購票去</button>
                    <button class="buy_btn buy_btn-buy" v-if="login == true && couponUsable == true && couponQualified == false" data-bs-toggle="modal" data-bs-target="#modal_pass">購票去</button>
                    <button class="buy_btn buy_btn-buy" v-if="login == true && couponUsable == true && couponQualified == true" @click="saleDate(); goBuy = true;">購票去</button>
                    <button class="login buy_btn buy_btn-buy" v-if="login != true && ticketDate == 'a'">購票去</button>
                    <button class="buy_btn buy_btn-buy" v-if="login != true && ticketDate != 'a'" @click="saleDate(); goBuy = true;">購票去</button>
                    <!--<button class="buy_btn buy_btn-buy" style="background: gray;" @click="alert('本系統將於 01:00-04:00 進行系統升級與更新，敬請見諒。');">購票去</button>-->
                </article>
                <article class="buy_box buy_box-check" :class="{ 'd-none': !goBuy }">
                    <button class="buy_btn buy_btn-undo" @click="goBuy = false;">
                        <img class="mx-auto" src="./img/undo.png" alt="undo" width="24" height="24">
                    </button>
                    <button class="buy_btn buy_btn-check" @click="buyTicket();">
                        NTD$
                        {{
                            farmat(
                                Math.ceil((20 * couponPrice)) * ticketTest + 
                                300 * ticketDiscount + 
                                300 * ticketEarly + 
                                Math.ceil((360 * couponPrice)) * ticketNormal + 
                                600 * ticketFamily
                            ) 
                        }}
                        結帳
                    </button>
                </article>
            </article>
            <!-- ticket_model -->
            <article class="ticket_model">
                <h3>本網站使用事項</h3>
                <p>本網站使用cookie，繼續使用即代表您同意我們使用cookie為您帶來更好的體驗，並在使用過程中幫助您註冊為用戶。購買本站任何票券視同同意相關購買條款。</p>
                <a href="./policy.php" target="_blank">更多詳情</a>
                <button id="model_hide">我同意，並繼續瀏覽本網站</button>
            </article>
            <!-- 更新誠品卡號 -->
            <article id="modal_eslite" class="modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">新增/修改誠品會員卡號</h5>
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="buy_subtitle">此欄為目前持有卡號，若無卡號則為空白。</p>
                            <input id="eslite_code" type="phone" placeholder="請輸入卡號" class="buy_coupon mb-0" :value="esliteCode">
                            <p class="buy_subtitle mt-3">*若卡號有誤可進行修改 (限乙次)</p>
                            <button class="buy_btn buy_btn-buy d-block mx-auto" data-update="true" @click="esliteUpdate();">送出</button>
                            <p class="buy_subtitle mt-3 mb-0">有任何問題，請聯繫客服hello@amping.io</p>
                        </div>
                    </div>
                </div>
            </article>
            <!-- 確認折扣碼密碼 -->
            <article id="modal_pass" class="modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">請輸入優惠碼</h5>
                            <button id="modal_pass-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input id="eslite_code" type="phone" placeholder="輸入優惠碼" class="buy_coupon mb-0" v-model="couponTry">
                            <section class="row g-1 mt-3">
                                <div class="col-6">
                                    <button class="buy_btn buy_btn-buy d-block mx-auto" style="background: gray;" @click="location.replace('./index.php');">無優惠碼</button>
                                </div>
                                <div class="col-6">
                                    <button class="buy_btn buy_btn-buy d-block mx-auto" data-update="true" @click="couponPass();">送出</button>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </main>
    <?php require_once("./components/source.php"); ?>
</body>

</html>