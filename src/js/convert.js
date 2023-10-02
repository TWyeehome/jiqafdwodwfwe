import Vue from 'vue'; // 引入 vue
import axios from 'axios';
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js'); // 引入 bootstrap 

const app = new Vue({
    el: '#vueApp',
    data: {
        login: false, // 登入狀態
        memberCode: 0, // 會員編碼
        esliteCode: '', // 誠品卡號
        filled: false, // 是否填寫過
        way: 'n',
        contact: '',
        info: '',
    },
    mounted() {
        this.formCheck(); // 確認是否填過
        this.loginStatus(); // 確認登入狀態
        this.load();
    },
    methods: {
        // 確認是否填過
        formCheck() {
            const match = document.cookie.match(new RegExp('(^| )' + 'convert' + '=([^;]+)'));
            console.log(match);
            if (match) {
                this.filled = true;
                this.contact = match[2];
                let formData = new FormData();
                formData.append('type', 'check');
                formData.append('contact', this.contact.replace(/%40/g, '@'));
                axios({
                    method: 'post',
                    url: './api/convert.php',
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                    .then((response) => {
                        let data = response.data;
                        console.log(data);
                        let way = '誠品';
                        if (data.way == 'a') way = '誠品官方售票平台';
                        if (data.way == 'b') way = 'udn售票網';
                        if (data.way == 'c') way = 'Klook';
                        if (data.way == 'd') way = 'ibon售票系統';
                        this.info = '購票管道 - ' + way + '<br>' + data.created_at + ' 入場';
                        console.log(this.info);
                    })
                    .catch((response) => {
                        console.log(response);
                    });
            } else {
                this.filled = false;
            };
        },
        // 送出表單
        send() {
            if (document.getElementById('way_1').checked) this.way = 'a';
            if (document.getElementById('way_2').checked) this.way = 'b';
            if (document.getElementById('way_3').checked) this.way = 'c';
            if (document.getElementById('way_4').checked) this.way = 'd';
            let formData = new FormData();
            formData.append('type', 'send');
            formData.append('way', this.way);
            formData.append('contact', this.contact);
            axios({
                method: 'post',
                url: './api/convert.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                    if (data.result === 'fail') {
                        return alert(data.message);
                    };
                    this.formCheck(); // 重新渲染
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 確認登入狀態
        loginStatus() {
            let formData = new FormData();
            formData.append('type', 'check');
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                    const headerMenu = document.querySelectorAll('.Mainheader__menubtn__box__menu');
                    // 登入中
                    if (data.result === 'success') {
                        // 登入狀態
                        this.login = true;
                        // 會員編碼
                        this.memberCode = data.member_code;
                        for (let i = 0; i < headerMenu.length; i++) {
                            headerMenu[i].innerHTML =
                                `<ul>
                                    <li class="d-flex justify-content-end align-items-center">
                                        <div class="event" style="cursor: pointer;">WALLET</div>
                                        <div class="menu_toggle">
                                            <img class="ms-1" style="cursor: pointer;" src="./img/menu.png" alt="logout" width="14" height="14" id="menu_` + i + `" data-bs-toggle="dropdown" aria-expanded="false">
                                            <ul class="dropdown-menu" aria-labelledby="menu_` + i + `">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center justify-content-end" href="./profile.php">
                                                        <p>我的帳戶</p>
                                                        <img src="./img/head.png" alt="head" width="14" height="14">
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item logout" href="#">
                                                        <p>登出</p>
                                                        <img src="./img/logout.png" alt="logout" width="14" height="14">
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>`;
                        };
                        // 登出
                        for (let i = 0; i < document.querySelectorAll('.logout').length; i++) {
                            document.querySelectorAll('.logout')[i].addEventListener('click', () => {
                                let formData = new FormData();
                                formData.append('type', 'logout');
                                axios({
                                    method: 'post',
                                    url: './api/member.php',
                                    data: formData,
                                    headers: { 'Content-Type': 'multipart/form-data' },
                                })
                                    .then((response) => {
                                        console.log(response.data);
                                        location.replace('./index.php');
                                    })
                                    .catch((response) => {
                                        console.log(response);
                                    });
                            });
                        };
                        // 前往活動頁
                        for (let i = 0; i < document.querySelectorAll('.event').length; i++) {
                            document.querySelectorAll('.event')[i].addEventListener('click', () => {
                                location.replace('./event.php');
                            });
                        };
                    } else {
                        this.login = false;
                        const headerMenu = document.querySelectorAll('.Mainheader__menubtn__box__menu');
                        for (let i = 0; i < headerMenu.length; i++) {
                            headerMenu[i].innerHTML = '<ul><li class="login" style="cursor: pointer;">LOGIN</li></ul>';
                        };
                        // 登入
                        const url = new URL(location.href);
                        let urlCoupon = url.searchParams.get('coupon');
                        let couponCode = '';
                        if (urlCoupon) { couponCode = '?coupon=' + urlCoupon; };
                        for (let i = 0; i < document.querySelectorAll('.login').length; i++) {
                            document.querySelectorAll('.login')[i].addEventListener('click', () => {
                                location.href = './prove_phone.php' + couponCode;
                            });
                        };
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 同意 cookie 條款
        cookie() {
            // 條款
            const body = document.querySelector('body');
            const model = document.querySelector('.ticket_model');
            const fade = document.querySelector('.popup_fade');
            const match = document.cookie.match(new RegExp('(^| )' + 'terms' + '=([^;]+)'));
            if (match == null) {
                console.log('沒有撈到 cookie');
                body.classList.add('scroll-disable');
                document.getElementById('model_hide').addEventListener('click', () => {
                    document.cookie = 'terms=agree; max-age=999999';
                    body.classList.remove('scroll-disable');
                    model.classList.add('d-none');
                    fade.classList.add('d-none');
                });
            } else {
                body.classList.remove('scroll-disable');
                model.classList.add('d-none');
                fade.classList.add('d-none');
            };
        },
        // 載入後
        load() {
            // 開始渲染
            document.getElementById('mainpage').classList.add('mainpage--pass');
            // 滾動監聽
            this.scroll();
            this.cookie();
        },
        // 滾動監聽
        scroll() {
            let lastScrollTop = 0;
            let body = document.querySelector('body');
            window.addEventListener('scroll', () => {
                // console.log(window.scrollY);
                let st = window.pageYOffset || document.documentElement.scrollTop;
                if (st > lastScrollTop) {
                    // console.log('往下滾');
                    if (window.scrollY >= 120) body.classList.add('--EFmainheaderShow'); // 判斷 header
                } else {
                    // console.log('往上滾');
                    if (window.scrollY < 120) body.classList.remove('--EFmainheaderShow'); // 判斷 header
                };
                lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling
            }, false);
        },
    }
});