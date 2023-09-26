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

<body class="--TrackModeActiveoff --TrackTagActiveoff --EFmainheaderShowoff --EFmenuBtnShowoff">
    <main id="vueApp" class="Layout --KIRE__wrapper">
        <section id="mainpage">
            <article class="px-0 Layout__MobileArea Layout__container" style="float: none; margin: auto;">
                <div class="Mainheader --fixed bg-white" style="left: 0; right: 0;">
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
                    <?php require_once("./components/header.php"); ?>
                    <!-- 我的帳戶 -->
                    <section class="ticket_box" style="border-bottom: 0px;">
                        <h2 class="ticket_title text-center" style="padding-bottom: 16px;">我的帳戶</h2>
                        <!-- 暱稱 -->
                        <article class="profile_box d-flex justify-content-between align-items-center">
                            <div>
                                <label class="profile_label">暱稱</label>
                                <input class="profile_input" data-edit="name" v-model="name">
                            </div>
                            <!---->
                            <img class="profile_edit" src="./img/edit.png" alt="edit" width="24" height="24" data-edit="name">
                            <button class="profile_save d-none" data-save="name">儲存</button>
                        </article>
                        <!-- 行動電話 -->
                        <article class="profile_box d-flex justify-content-between align-items-center">
                            <div>
                                <label class="profile_label">行動電話</label>
                                <input class="profile_input" data-edit="phone" v-model="phone">
                                <input class="profile_code" placeholder="請輸入驗證碼" data-pass="phone" v-model="phoneCode" :class="{ 'd-none': phoneCheck != true }">
                                <div class="d-flex mt-1" v-if="phonePass == 'true'">
                                    <label class="profile_tag">驗證成功</label>
                                </div>
                                <div class="d-flex mt-1" v-if="phonePass != 'true'">
                                    <label class="profile_tag">尚未驗證</label>
                                    <label class="profile_tag">
                                        <a href="#" data-pass="phone" @click="phoneCheck = true; reset(); sendCode('phone');">發送驗證碼</a>
                                    </label>
                                </div>
                            </div>
                            <!---->
                            <div :class="{ 'd-none': phoneCheck == true }">
                                <img class="profile_edit" src="./img/edit.png" alt="edit" width="24" height="24" data-edit="phone">
                            </div>
                            <button class="profile_save d-none" data-save="phone">儲存</button>
                            <button class="profile_check" data-pass="phone" :class="{ 'd-none': phoneCheck != true }" @click="checkCode('phone');">驗證</button>
                        </article>
                        <!-- 電子郵件 -->
                        <article class="profile_box d-flex justify-content-between align-items-center">
                            <div>
                                <label class="profile_label">電子郵件</label>
                                <input class="profile_input" data-edit="email" v-model="email">
                                <input class="profile_code" placeholder="請輸入驗證碼" data-pass="email" v-model="emailCode" :class="{ 'd-none': emailCheck != true }">
                                <div class="d-flex mt-1" v-if="emailPass == 'true'">
                                    <label class="profile_tag">驗證成功</label>
                                </div>
                                <div class="d-flex mt-1" v-if="emailPass != 'true'">
                                    <label class="profile_tag">尚未驗證</label>
                                    <label class="profile_tag">
                                        <a href="#" data-pass="email" @click="emailCheck = true; reset(); sendCode('email');">發送驗證碼</a>
                                    </label>
                                </div>
                            </div>
                            <!---->
                            <div :class="{ 'd-none': emailCheck == true }">
                                <img class="profile_edit" src="./img/edit.png" alt="edit" width="24" height="24" data-edit="email">
                            </div>
                            <button class="profile_save d-none" data-save="email">儲存</button>
                            <button class="profile_check" data-pass="email" :class="{ 'd-none': emailCheck != true }" @click="checkCode('email');">驗證</button>
                        </article>
                        <!-- 誠品會員卡號 -->
                        <article class="profile_box d-flex justify-content-between align-items-center">
                            <div>
                                <label class="profile_label">誠品會員卡號</label>
                                <input class="profile_input" placeholder="新增誠品會員卡號" data-edit="eslite" v-model="esliteCode">
                                <p id="eslite_notice" class="profile_label d-none mt-1 mb-0">*若卡號有誤可進行修改 (限乙次)</p>
                                <div class="d-flex mt-1">
                                    <label class="profile_tag">輸入誠品卡號 門票九折優惠！</label>
                                    <label class="profile_tag"><a target="_blank" href="https://meet.eslite.com/tw/tc/member/memberbenefits">註冊誠品會員</a></label>
                                </div>
                            </div>
                            <!---->
                            <img class="profile_edit" src="./img/edit.png" alt="edit" width="24" height="24" data-edit="eslite">
                            <button class="profile_save d-none" data-save="eslite">儲存</button>
                        </article>
                    </section>
                    <!-- 收不到驗證碼? -->
                    <section class="ticket_box" style="border-bottom: 0px;">
                        <h2 class="ticket_title text-center" style="padding-bottom: 16px;">收不到驗證碼?</h2>
                        <p class="ticket_content d-flex p-0" style="margin-bottom: 16px; font-size: 12px;">電子郵件<br>請查詢垃圾信件，並把 info@amping.io 加入安全名單內。</p>
                        <p class="ticket_content d-flex mb-0 p-0" style="font-size: 12px;">行動電話<br>請查詢是否被歸類為 SPAM 或者被電信商阻擋，若持續無法收到手機驗證碼，請使用另外門號嘗試。</p>
                    </section>
                    <!-- 重新綁定 -->
                    <section class="ticket_box">
                        <h2 class="ticket_title text-center" style="padding-bottom: 16px;">重新綁定<br>新的 行動電話 或 電子郵件</h2>
                        <p class="ticket_content mb-0 p-0" style="font-size: 12px;">由於結帳資訊與您的帳號是關聯的，如欲更改，請透過<a class="d-flex" href="mailto:hello@amping.io">hello@amping.io</a>聯繫客服</p>
                    </section>
                    <?php require_once("./components/footer.php"); ?>
                </main>
            </article>
        </section>
    </main>
    <?php require_once("./components/source.php"); ?>
</body>

</html>