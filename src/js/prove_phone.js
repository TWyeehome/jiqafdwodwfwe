
import Vue from 'vue'; // 引入 vue
import axios from 'axios';

const app = new Vue({
    el: '#vueapp',
    data: {
        name: '',
        phoneNumber: '',
        email: '',
        esliteCheck: false, // 是否為誠品會員 
        esliteCode: '', // 誠品會員卡號
        proveCode: '',
        check: false,
        sendLimit: 0, // 送出次數
        register: false, // 註冊
        // 信箱登入模式
        emailMode: false,
        ticketDate: '', // 票券販售時段 (n: 測試 a: 超級早鳥 b: 早鳥 c: 其他 d: 售完)
    },
    mounted() {
        this.checkDeive(); // 確認裝置
        this.loginStatus();
    },
    methods: {
        // 判斷售票期間
        saleDate() {
            // 超早鳥賣到 8/22 ~ 8/24 23:59:59
            // 早鳥賣到 8/25 ~ 8/28 23:59:59
            // 其他票 8/29 開始販售
            let now = new Date().getTime();
            let superDay = Math.floor((new Date('8/31/2023 12:00').getTime() - now) / (1000 * 60));
            let earlyDay = Math.floor((new Date('9/3/2023').getTime() - now) / (1000 * 60));
            let otherDay = Math.floor((new Date('9/7/2023').getTime() - now) / (1000 * 60));
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
        },
        checkDeive() {
            let mobileDevices = ['Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone', 'iPhone Simulator', 'iPhone Simulator', 'iPod Simulator', 'iPad Simulator', 'Pike v7.6 release 92', 'Pike v7.8 release 517'];
            for (let i = 0; i < mobileDevices.length; i++) {
                if (navigator.userAgent.match(mobileDevices[i])) {
                    document.querySelector('.pack').classList.remove('pack--pc');
                };
            };
            // 初始化
            this.phoneNumber = '';
            this.email = '';
        },
        loginStatus() {
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
                    console.log(response.data);
                    // 若登入過
                    if (response.data.result === 'success') {
                        return location.replace('./index.php' + couponUrl);
                    };
                    // 若沒登入過
                    this.saleDate();
                    document.querySelector('.pack').classList.remove('d-none');
                    // 前往活動頁
                    for (let i = 0; i < document.querySelectorAll('.btn_home').length; i++) {
                        document.querySelectorAll('.btn_home')[i].addEventListener('click', () => {
                            location.href = './index.php' + couponUrl;
                        });
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 驗證碼監聽
        code(event) {
            console.log(event.target.value);
            if (event.target.value.length == 6) {
                const send = document.querySelectorAll('.buy_send');
                for (let i = 0; i < send.length; i++) {
                    send[i].disabled = true;
                    send[i].textContent = 'loading...';
                };
                // call API
                this.sendCode();
                return
            };
        },
        // 送出鈕初始化
        sendDefault(error) {
            const send = document.querySelectorAll('.buy_send');
            for (let i = 0; i < send.length; i++) {
                send[i].disabled = false;
                send[i].textContent = '送出';
            };
            if (error) { alert(error); };
        },
        // 送出表單
        send() {
            const send = document.querySelectorAll('.buy_send');
            for (let i = 0; i < send.length; i++) {
                send[i].disabled = true;
                send[i].textContent = 'loading...';
            };
            console.log('emailMode: ' + this.emailMode);
            /* 信箱模式 */
            if (this.emailMode) {
                if (!this.check) {
                    if (this.sendLimit > 20) {
                        return this.sendDefault('請勿在短時間內大量寄出驗證信件');
                    };
                    if (!this.email) {
                        return this.sendDefault('請填寫信箱');
                    };
                    if (this.email.length < 4) {
                        return this.sendDefault('請輸入有效信箱');
                    };
                    // call API
                    let formData = new FormData();
                    formData.append('type', 'login');
                    formData.append('mode', 'email');
                    formData.append('email', this.email);
                    axios({
                        method: 'post',
                        url: './api/member.php',
                        data: formData,
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                        .then((response) => {
                            this.sendLimit += 1;
                            console.log(response.data);
                            let data = response.data;
                            if (data.result === 'success') {
                                this.check = true;
                            } else {
                                this.check = false;
                                alert(data.message);
                            };
                            this.sendDefault();
                        })
                        .catch((response) => {
                            console.log(response);
                        });
                } else {
                    this.sendCode();
                };
                return
            };
            /* 手機模式 */
            if (!this.check) {
                if (this.sendLimit > 20) {
                    return this.sendDefault('請勿在短時間內大量寄出驗證簡訊');
                };
                if (!this.phoneNumber) {
                    return this.sendDefault('請填寫手機號碼');
                };
                if (this.phoneNumber.length < 9) {
                    return this.sendDefault('請輸入有效手機號碼');
                };
                // call API
                let formData = new FormData();
                formData.append('type', 'login');
                formData.append('mode', 'phone');
                formData.append('number', this.phoneNumber);
                axios({
                    method: 'post',
                    url: './api/member.php',
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                    .then((response) => {
                        // api
                        this.sendLimit += 1;
                        console.log(response.data);
                        let data = response.data;
                        if (data.result === 'success') {
                            this.check = true;
                            // 
                            const url = new URL(location.href);
                            let urlCoupon = url.searchParams.get('coupon');
                            let couponCode = '';
                            if (urlCoupon) { couponCode = '?coupon=' + urlCoupon; };
                            // 簡訊發送失敗
                            if (data.log) {
                                if (data.log.match('Error')) {
                                    this.phoneNumber = '';
                                    this.sendDefault('簡訊驗證碼發送失敗，請輸入有效的手機號碼，或回報主辦單位');
                                    location.href = './prove_phone.php' + couponCode;
                                    return
                                };
                            };
                        } else {
                            this.check = false;
                            alert(data.message);
                        };
                        this.sendDefault();
                    })
                    .catch((response) => {
                        console.log(response);
                    });
            } else {
                this.sendCode();
            };
        },
        // 送出驗證碼
        sendCode() {
            if (!this.proveCode) {
                return this.sendDefault('請輸入驗證碼');
            };
            if (this.proveCode.length != 6) {
                return this.sendDefault('驗證碼為 6 位數');
            };
            let formData;
            if (this.emailMode) {
                /* 信箱模式 */
                formData = new FormData();
                formData.append('type', 'checkCode');
                formData.append('mode', 'email');
                formData.append('email', this.email);
                formData.append('code', this.proveCode);
            } else {
                /* 手機模式 */
                formData = new FormData();
                formData.append('type', 'checkCode');
                formData.append('mode', 'phone');
                formData.append('number', this.phoneNumber);
                formData.append('code', this.proveCode);
            };
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    console.log(response.data);
                    let data = response.data;
                    if (data.result === 'success') {
                        // 
                        const url = new URL(location.href);
                        let urlCoupon = url.searchParams.get('coupon');
                        let couponCode = '';
                        if (urlCoupon) { couponCode = '?coupon=' + urlCoupon; };
                        // 註冊完成
                        if (data.register == 'true') {
                            location.replace('./index.php' + couponCode);
                            return
                        } else {
                            // 註冊未完成
                            this.register = true;
                            this.sendDefault();
                        };
                    } else {
                        this.proveCode = '';
                        this.sendDefault(data.message);
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 前往註冊
        registerCheck() {
            // 檢查誠品會員
            if (this.esliteCheck) {
                if (!this.esliteCode) {
                    return this.sendDefault('請輸入誠品會員卡號');
                };
            } else {
                this.esliteCode = '';
            };
            // 檢查姓名
            if (this.name.length > 20) {
                return this.sendDefault('姓名不得超過 20 個字');
            };
            // 檢查號碼
            if (this.phoneNumber.length < 9) {
                return this.sendDefault('請輸入完整手機號碼');
            };
            // 檢查信箱
            function validateEmail(email) {
                /*var re = /\S+@\S+\.\S+/;
                return re.test(email);*/
            };
            if (!validateEmail(this.email)) {
                return this.sendDefault('請輸入有效信箱');
            };
            // 模式
            let mode = 'phone';
            if (this.emailMode) { mode = 'email'; };
            // call API
            document.getElementById('btn_register').disabled = true;
            let formData = new FormData();
            formData.append('type', 'register');
            formData.append('mode', mode);
            formData.append('name', this.name);
            formData.append('number', this.phoneNumber);
            formData.append('email', this.email);
            formData.append('esliteCode', this.esliteCode); // 誠品卡號
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    document.getElementById('btn_register').disabled = false;
                    console.log(response.data);
                    let data = response.data;
                    // 
                    const url = new URL(location.href);
                    let urlCoupon = url.searchParams.get('coupon');
                    let couponCode = '';
                    if (urlCoupon) { couponCode = '?coupon=' + urlCoupon; };
                    // 註冊成功
                    if (data.result === 'success') {
                        return location.href = './index.php' + couponCode;
                    };
                    // 失敗
                    alert(data.message);
                })
                .catch((response) => {
                    console.log(response);
                });
        },
    }
});