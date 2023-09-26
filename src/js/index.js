import Vue from 'vue'; // 引入 vue
import axios from 'axios';
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js'); // 引入 bootstrap 

const app = new Vue({
    el: '#vueApp',
    data: {
        login: false, // 登入狀態
        memberCode: 0, // 會員編碼
        esliteCode: '', // 誠品卡號
        btnStatus: '前往購買', // 購買按鈕狀態
        goBuy: false, // 前往購買
        ticketTest: 0, // 測試票
        ticketDiscount: 0, // 超級早鳥票
        ticketEarly: 0, // 早鳥票
        ticketNormal: 0, // 全票
        ticketFamily: 0, // 親子套票
        companyName: '', // 公司抬頭
        companyPay: false, // 是否需要開立統一編號?
        companyId: '', // 統一編號
        couponTry: '', // 書用者輸入的折扣碼密語
        couponPassword: '', // 折扣碼密語
        couponUsable: false, // 折扣碼是否有用
        couponQualified: false, // 密語通過，折扣碼有資格使用
        coupon: '', // 折扣碼
        couponName: '', // 折扣碼名稱
        couponPrice: 1, // 折扣碼價格
        couponStatus: '', // 折扣碼優惠方案
        ticketDate: '', // 票券販售時段 (n: 測試 a: 超級早鳥 b: 早鳥 c: 其他 d: 售完)
    },
    mounted() {
        this.loginStatus(); // 確認登入狀態
        this.saleDate(); // 判斷票券開賣日
        // this.company(); // 檢查公司行號與統一編號是否有對上
    },
    methods: {
        // 確認登入狀態
        loginStatus() {
            // alert('本系統將於 01:00-04:00 進行系統升級與更新，敬請見諒。');
            // 判斷折扣碼網址
            const coupon = new URL(location.href).searchParams.get('coupon');
            let couponUrl = '';
            if (coupon) { couponUrl = '?coupon=' + coupon; };
            // call API
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
                        // 若有誠品卡號
                        if (data.eslite_code) {
                            this.esliteCode = data.eslite_code;
                        };
                        for (let i = 0; i < headerMenu.length; i++) {
                            headerMenu[i].innerHTML =
                                `<ul>
                                    <li class="d-flex justify-content-end align-items-center">
                                        <div class="event" style="cursor: pointer;">票匣</div>
                                        <div class="menu_toggle dropstart">
                                            <img class="ms-3" style="cursor: pointer;" src="./img/menu.png" alt="logout" width="20" id="menu_` + i + `" data-bs-toggle="dropdown" aria-expanded="false">
                                            <ul class="dropdown-menu" aria-labelledby="menu_` + i + `">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center justify-content-end" href="./profile.php` + couponUrl + `">
                                                        <p>我的帳戶</p>
                                                        <img src="./img/head.png" alt="head" width="14">
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item logout" href="#">
                                                        <p>登出</p>
                                                        <img src="./img/logout.png" alt="logout" width="14">
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
                                        location.replace('./index.php' + couponUrl);
                                    })
                                    .catch((response) => {
                                        console.log(response);
                                    });
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
                    // 前往活動頁
                    for (let i = 0; i < document.querySelectorAll('.event').length; i++) {
                        document.querySelectorAll('.event')[i].addEventListener('click', () => {
                            location.href = './event.php' + couponUrl;
                        });
                    };
                    this.load(); // 開始訊染
                    this.couponCheck('link');
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 判斷是否為早鳥期間
        saleDate() {
            // 超早鳥賣到 8/22 ~ 8/24 23:59:59
            // 早鳥賣到 8/25 ~ 8/28 23:59:59
            // 其他票 8/29 開始販售
            let now = new Date().getTime();
            let superDay = Math.floor((new Date('8/31/2023 12:00').getTime() - now) / (1000 * 60));
            let earlyDay = Math.floor((new Date('9/3/2023').getTime() - now) / (1000 * 60));
            let otherDay = Math.floor((new Date('9/6/2023').getTime() - now) / (1000 * 60));
            let endDay = Math.floor((new Date('1/14/2024').getTime() - now) / (1000 * 60));
            // 尚未開賣
            if (superDay > 0) {
                // this.ticketDate = 'n';
                document.cookie = 'ticketDiscount=0; max-age=0';
                document.cookie = 'ticketEarly=0; max-age=0';
                document.cookie = 'ticketNormal=0; max-age=0';
                document.cookie = 'ticketFamily=0; max-age=0';
            };
            // 超級早鳥開賣
            if (superDay <= 0 && earlyDay > 0) {
                this.ticketDate = 'a';
                document.cookie = 'ticketEarly=0; max-age=0';
                document.cookie = 'ticketNormal=0; max-age=0';
                document.cookie = 'ticketFamily=0; max-age=0';
            };
            // 早鳥開賣
            if (earlyDay <= 0 && otherDay > 0) {
                this.ticketDate = 'b';
                document.cookie = 'ticketDiscount=0; max-age=0';
                document.cookie = 'ticketNormal=0; max-age=0';
                document.cookie = 'ticketFamily=0; max-age=0';
            };
            // 全票、親子開賣
            if (otherDay <= 0 && endDay > 0) {
                this.ticketDate = 'c';
                document.cookie = 'ticketDiscount=0; max-age=0';
                document.cookie = 'ticketEarly=0; max-age=0';
            };
            // 售完
            if (otherDay <= 0 && endDay < 0) {
                this.ticketDate = 'd';
            };
            console.log('超早鳥: ' + superDay);
            console.log('早鳥: ' + earlyDay);
            console.log('全票: ' + otherDay);
            console.log('結束販售倒數: ' + endDay);
            console.log('ticketDate: ' + this.ticketDate);
            this.cookie(); // 使用者條款
        },
        // 判斷特殊字元
        textCheck(string) {
            let specialCharacters = ['@', ',', '+', '-', , '*', '/', '&', '=', ' ', '#', '!', '?']
            for (let i = 0; i < specialCharacters.length; i++) {
                if (string.indexOf(specialCharacters[i]) >= 0) {
                    return false
                };
            };
        },
        // 確認折扣碼密語
        couponPass() {
            if (this.couponTry == this.couponPassword) {
                this.couponQualified = true;
                document.getElementById('modal_pass-close').click();
                this.goBuy = true;
            } else {
                alert('密語錯誤，請重新輸入。');
                this.couponTry = '';
            };
        },
        // 確認折扣碼
        couponCheck(type) {
            // 來自分享連結
            if (type === 'link') {
                const coupon = new URL(location.href).searchParams.get('coupon');
                // 若網址有帶入參數
                if (coupon && !this.coupon) {
                    this.coupon = coupon;
                    console.log('coupon: ' + this.coupon);
                };
                // 若網址沒帶入折扣碼參數
                if (!coupon) {
                    console.log('網址沒帶入折扣碼參數');
                    this.couponName = '';
                    this.couponPrice = 1;
                    this.couponStatus = '';
                    // return
                };
            };
            // call API
            let formData = new FormData();
            formData.append('type', 'coupon_check');
            formData.append('code', this.coupon);
            axios({
                method: 'post',
                url: './api/ticket.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    console.log(response.data);
                    let data = response.data;
                    // 若錯誤
                    if (data.result === 'fail') {
                        this.couponUsable = false;
                        this.couponName = '';
                        this.couponStatus = '';
                        this.couponPrice = 1;
                        // 是否已登入返回購買
                        let match = document.cookie.match(new RegExp('(^| )' + 'buyAgain' + '=([^;]+)'));
                        if (match && this.login == true) { this.goBuy = true; };
                        console.log('goBuy: ' + this.goBuy);
                        return
                    };
                    this.coupon = data.coupon;
                    // 若採誠品會員 9 折方案
                    if (data.coupon === 'eslite_member') {
                        this.couponName = '優惠狀態 誠品會員<br>卡號 ' + this.esliteCode + '<br>全票優惠價折扣';
                        // 是否已登入返回購買
                        let match = document.cookie.match(new RegExp('(^| )' + 'buyAgain' + '=([^;]+)'));
                        if (match && this.login == true) { this.goBuy = true; };
                    } else {
                        this.couponName = '折扣碼: ' + data.coupon;
                        this.couponUsable = true;
                        this.couponPassword = data.pw;
                    };
                    if (data.coupon_discount == 1) {
                        // this.coupon = '';
                        this.couponName = '';
                        this.couponStatus = '';
                        this.couponPrice = 1;
                    };
                    if (data.coupon_discount == 0.95) {
                        this.couponStatus = '95 折';
                        this.couponPrice = 0.95;
                    };
                    if (data.coupon_discount == 0.9) {
                        this.couponStatus = '9 折';
                        this.couponPrice = 0.9;
                    };
                    if (data.coupon_discount == 0.85) {
                        this.couponStatus = '85 折';
                        this.couponPrice = 0.85;
                    };
                    if (data.coupon_discount == 0.8) {
                        this.couponStatus = '8 折';
                        this.couponPrice = 0.8;
                    };
                    // 紀錄點擊率
                    if (type === 'link') {
                        // output api
                        let formData = new FormData();
                        formData.append('type', 'coupon_clicked');
                        formData.append('code', this.coupon);
                        axios({
                            method: 'post',
                            url: './api/ticket.php',
                            data: formData,
                            headers: { 'Content-Type': 'multipart/form-data' },
                        })
                            .then((response) => {
                                console.log(response.data);
                                console.log('goBuy: ' + this.goBuy);
                            })
                            .catch((response) => {
                                console.log(response);
                            });
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
            return
        },
        // 千分符
        farmat(mun) {
            if (mun === null) return;
            var m = parseInt(mun).toString();
            var len = m.length;
            if (len <= 3) return m;
            var n = len % 3;
            if (n > 0) {
                return m.slice(0, n) + "," + m.slice(n, len).match(/\d{3}/g).join(",")
            } else {
                return m.slice(n, len).match(/\d{3}/g).join(",")
            };
        },
        // 購買票
        buyTicket() {
            // 超級早鳥和早鳥期間不能使用折扣碼 
            if (this.ticketDate === 'a' || this.ticketDate === 'b') {
                this.coupon = '';
            };
            // 非超級早鳥期間不能買超級早鳥票
            if (this.ticketDate != 'a') { this.ticketDiscount = 0; };
            // 非早鳥期間不能買早鳥票
            if (this.ticketDate != 'b') { this.ticketEarly = 0; };
            // 未購買
            if (this.ticketTest + this.ticketEarly + this.ticketNormal + this.ticketDiscount + this.ticketFamily == 0) {
                return alert('請選擇購買票種');
            };
            // 尚未登入
            if (!this.login) {
                document.cookie = 'buyAgain=true;';
                const coupon = new URL(location.href).searchParams.get('coupon');
                if (coupon) {
                    location.href = './prove_phone.php?coupon=' + coupon;
                } else {
                    location.href = './prove_phone.php';
                };
                return
            };
            // 是否需要開立統一編號?
            if (!this.companyPay) {
                this.companyName = '';
                this.companyId = '';
            } else {
                if (!this.companyName && !this.companyId) {
                    return alert('請輸入完整公司抬頭及統一編號');
                };
                // 檢查公司抬頭
                if (this.companyName) {
                    if (this.companyName.length < 3) {
                        return alert('請輸入完整公司抬頭');
                    };
                    if (this.companyName.length > 60) {
                        return alert('公司抬頭最長為 60 個字元');
                    };
                    if (!this.companyId) {
                        return alert('請輸入統一編號');
                    };
                    if (isNaN(this.companyId) || this.companyId.length != 8) {
                        return alert('統一編號只能為八位數字');
                    };
                };
                // 檢查統一編號
                if (this.companyId) {
                    if (!this.companyName) {
                        return alert('請輸入公司抬頭');
                    };
                    if (isNaN(this.companyId) || this.companyId.length != 8) {
                        return alert('統一編號只能為八位數字');
                    };
                };
            };
            // 檢查折扣碼
            if (this.textCheck(this.coupon) == false) {
                return alert('折扣碼不得包含特殊字元或空格');
            };
            // 確認網址參數
            let formData = new FormData();
            formData.append('type', 'buy');
            formData.append('memberCode', this.memberCode);
            formData.append('esliteCode', this.esliteCode);
            formData.append('ticketTest', this.ticketTest); // 測試票
            formData.append('ticketDiscount', this.ticketDiscount); // 超級早鳥票
            formData.append('ticketEarly', this.ticketEarly); // 早鳥票
            formData.append('ticketNormal', this.ticketNormal); // 全票
            formData.append('ticketFamily', this.ticketFamily); // 親子套票
            formData.append('coupon', this.coupon); // 折扣碼
            formData.append('companyName', this.companyName); // 公司抬頭
            formData.append('companyId', this.companyId); // 統一編號
            axios({
                method: 'post',
                url: './api/ticket.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    console.log(response.data);
                    let data = response.data;
                    if (data.result === 'success') {
                        document.cookie = 'buyAgain=false; max-age=0';
                        location.href = './sample_All_CreateOrder.php' +
                            '?trade_no=' + data.trade_no +
                            '&ticketTest=' + this.ticketTest + // 測試票
                            '&ticketDiscount=' + this.ticketDiscount + // 超級早鳥票
                            '&ticketEarly=' + this.ticketEarly + // 早鳥票
                            '&ticketNormal=' + this.ticketNormal + // 全票
                            '&ticketFamily=' + this.ticketFamily + // 親子套票
                            '&esliteCode=' + this.esliteCode + // 誠品會員卡號
                            '&coupon=' + this.coupon + // 折扣碼
                            '&companyName=' + this.companyName + // 公司抬頭
                            '&companyId=' + this.companyId; // 統一編號
                            return
                    };
                    // 錯誤
                    alert(data.message);
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
            // 票券販售時段【超級早鳥】
            if (this.ticketDate === 'a') {
                document.cookie = 'ticketEarly=0; max-age=0';
                document.cookie = 'ticketNormal=0; max-age=0';
                document.cookie = 'ticketFamily=0; max-age=0';
            };
            // 票券販售時段【早鳥】
            if (this.ticketDate === 'b') {
                document.cookie = 'ticketDiscount=0; max-age=0';
                document.cookie = 'ticketNormal=0; max-age=0';
                document.cookie = 'ticketFamily=0; max-age=0';
            };
            // 票券販售時段【全票、親子套票】
            if (this.ticketDate === 'c') {
                document.cookie = 'ticketDiscount=0; max-age=0';
                document.cookie = 'ticketEarly=0; max-age=0';
            };
            const ticketEarly = document.cookie.match(new RegExp('(^| )' + 'ticketEarly' + '=([^;]+)'));
            if (ticketEarly) { this.ticketEarly = Number(ticketEarly[2]); };
            const ticketNormal = document.cookie.match(new RegExp('(^| )' + 'ticketNormal' + '=([^;]+)'));
            if (ticketNormal) { this.ticketNormal = Number(ticketNormal[2]); };
            const ticketDiscount = document.cookie.match(new RegExp('(^| )' + 'ticketDiscount' + '=([^;]+)'));
            if (ticketDiscount) { this.ticketDiscount = Number(ticketDiscount[2]); };
            const ticketFamily = document.cookie.match(new RegExp('(^| )' + 'ticketFamily' + '=([^;]+)'));
            if (ticketFamily) { this.ticketFamily = Number(ticketFamily[2]); };
            // 折扣碼
        },
        // 載入後
        load() {
            // 購票表單高度 (20230810 對上高度多 - 12px)
            document.querySelector('.buy_ticket').style.maxHeight =
                (window.innerHeight - document.querySelectorAll('.buy_box')[0].offsetHeight - document.querySelector('.Mainheader').offsetHeight - 8 - 12) + 'px';
            // 開始渲染
            document.getElementById('mainpage').classList.add('mainpage--pass');
            // 滾動監聽
            this.scroll();
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
        // 檢查公司行號與統一編號是否有對上
        company() {
            axios({
                method: 'get',
                url: 'https://data.gcis.nat.gov.tw/od/data/api/5F64D864-61CB-4D0D-8AD9-492047CC1EA6?$format=json&$filter=Business_Accounting_NO%20eq%2020828393&$skip=0&$top=50',
                data: null,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    console.log(response);
                })
                .catch((response) => {
                    console.log(response);
                });
        },
    }
});