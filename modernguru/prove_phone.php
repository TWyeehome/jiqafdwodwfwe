<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("./components/meta.php"); ?>
    <style>
        .close {
            cursor: pointer;
            position: absolute;
            top: 30px;
            right: 30px;
        }

        .bottom {
            position: absolute;
            bottom: 30px;
            right: 30px;
            margin: 0;
        }

        .notice {
            position: absolute;
            top: 30px;
            left: 30px;
            line-height: 30px;
        }
    </style>
</head>

<body>
    <main id="vueapp" class="pack pack--pc d-none">
        <!-- 手機模式 -->
        <section class="subject subject--form align-items-center justify-content-center d-flex" :class="{ 'd-none': register || emailMode}">
            <p class="notice">登入>完成結帳＞查看票匣(WALLET)</p>
            <img class="btn_home close" src="./img/close.png" alt="close" width="30" height="30" title="離開">
            <article class="w-100">
                <div :class="{ 'd-none': check }">
                    <p v-if="ticketDate == 'a'">*目前為誠品會員專屬超級早鳥票期間，請登入並輸入誠品會員卡號即可購票。</p>
                    <h3 class="subject__title mb-0">請輸入手機號碼</h3>
                    <input class="subject__input my-3" type="tel" placeholder="0912345678" v-model="phoneNumber">
                    <a href="#" class="bottom d-block text-center h6" style="color: gray; font-size: 10px;" @click="emailMode = true; check = check;">或 email 登入</a>
                </div>
                <div :class="{ 'd-none': !check }">
                    <h3 class="subject__title" style="margin-bottom: 16px;">請輸入驗證碼</h3>
                    <input type="tel" class="subject__input" placeholder="000000" v-model="proveCode" @input="code(event);">
                    <a href="#" class="d-block text-center h6 my-3" style="color: gray; font-size: 10px;" @click="location.reload();">未收到簡訊? 重新輸入門號</a>
                    <div class="bottom d-flex">
                        <a href="#" class="text-center h6 m-0" style="color: gray; font-size: 10px;" @click="emailMode = true; check = !check;">使用 email 登入</a>
                        <p class="text-center h6 mb-0 mx-2" style="color: gray; font-size: 10px;">或</p>
                        <a href="mailto: hello@amping.io" target="_blank" class="text-center h6 m-0" style="color: gray; font-size: 10px;">聯繫客服</a>
                    </div>
                </div>
                <button class="buy_send buy_btn buy_btn-check" @click="send();">送出</button>
                <p class="mt-3 mx-auto" :class="{ 'd-none': !check }" style="color: gray; font-size: 10px; width: 85%;">
                    簡訊驗證碼若被電信商阻擋時，請<a class="text-dark w-75 mx-auto" style="display: inline;" target="_blank" href="https://shareablecorp.notion.site/Modern-Guru-Q-A-24cbc83992694848884b7e05647df0fc">改用email</a>來收取驗證碼或向您的電信商確認服務。
                </p>
            </article>
        </section>
        <!-- 信箱模式 -->
        <section class="subject subject--form align-items-center justify-content-center d-flex" :class="{ 'd-none': register || !emailMode}">
            <p class="notice">登入>完成結帳＞查看票匣(WALLET)</p>
            <img class="btn_home close" src="./img/close.png" alt="close" width="30" height="30" title="離開">
            <article class="w-100">
                <div :class="{ 'd-none': check }">
                    <p v-if="ticketDate == 'a'">*目前為誠品會員專屬超級早鳥票期間，請登入並輸入誠品會員卡號即可購票。</p>
                    <h3 class="subject__title mb-0">請輸入 email 帳號</h3>
                    <input class="subject__input my-3" type="email" placeholder="email@example.com" v-model="email">
                    <a href="#" class="bottom d-block text-center h6" style="color: gray; font-size: 10px;" @click="emailMode = !emailMode;">或手機登入</a>
                </div>
                <div :class="{ 'd-none': !check }">
                    <h3 class="subject__title" style="margin-bottom: 16px;">請輸入驗證碼</h3>
                    <input type="tel" class="subject__input mb-3" placeholder="000000" v-model="proveCode" @input="code(event);">
                    <div class="bottom d-flex">
                        <a href="#" class="text-center h6 m-0" style="color: gray; font-size: 10px;" @click="emailMode = false; check = !check;">使用手機登入</a>
                        <p class="text-center h6 mb-0 mx-2" style="color: gray; font-size: 10px;">或</p>
                        <a href="mailto: hello@amping.io" target="_blank" class="text-center h6 m-0" style="color: gray; font-size: 10px;">聯繫客服</a>
                    </div>
                </div>
                <button class="buy_send buy_btn buy_btn-check" @click="send();">送出</button>
                <a href="#" class="d-block text-center h6 my-3" style="pointer-events: none; text-decoration: none; color: gray; font-size: 10px;" :class="{ 'd-none': check }">請檢查垃圾信件或將 info@amping.io 設為安全名單</a>
            </article>
        </section>
        <!-- 註冊 -->
        <section class="subject subject--form align-items-center justify-content-center d-flex" :class="{ 'd-none': !register}">
            <article class="w-100">
                <section>
                    <h3 class="subject__title mb-0">完成購票流程</h3>
                    <input class="subject__input mt-3 border-bottom-0" type="name" placeholder="請輸入姓名" v-model="name">
                    <input class="subject__input mb-0" type="tel" placeholder="請輸入手機號碼" v-model="phoneNumber" :class="{ 'd-none': !emailMode}">
                    <input class="subject__input mb-0" type="email" placeholder="請輸入信箱" v-model="email" :class="{ 'd-none': emailMode}">
                    <input class="subject__input mb-0 border-top-0" type="tel" placeholder="請輸入誠品會員卡號" v-model="esliteCode" :class="{ 'd-none': !esliteCheck}">
                </section>
                <!-- 是否為誠品會員? -->
                <div class="form-check d-flex justify-content-center my-4">
                    <input id="eslite" class="form-check-input me-2" type="checkbox" v-model="esliteCheck">
                    <label for="eslite" class="form-check-label">是否為誠品會員?</label>
                </div>
                <p class="mb-0">加入誠品會員即享購票優惠。<br>※卡號顯示於誠品APP-會員中心(會員資料)。</p>
                <p class="ticket_content text-center mt-3"><a target="_blank" href="https://meet.eslite.com/tw/tc/member/memberbenefits">線上申辦會員</a></p>
                <button id="btn_register" class="buy_send buy_btn buy_btn-check" @click="registerCheck();">送出</button>
                <p class="ticket_content text-center mt-3"><a href="./index.php">回購票首頁</a></p>
            </article>
        </section>
    </main>
    <?php require_once("./components/source.php"); ?>
</body>

</html>