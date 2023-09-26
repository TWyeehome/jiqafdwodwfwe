import Vue from 'vue'; // 引入 vue
import axios from 'axios';
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js'); // 引入 bootstrap 

const app = new Vue({
    el: '#vueApp',
    data: {
        login: false, // 登入狀態
        memberCode: '', // 會員編碼
        memberId: '', // 會員 id
        name: '',
        phone: '',
        phoneCheck: false, // 信箱驗證
        phoneCode: '',
        phonePass: '',
        email: '',
        emailCheck: false, // 信箱驗證
        emailCode: '',
        emailPass: '',
        esliteCode: '', // 誠品卡號
        esliteCounts: '', // 誠品卡號更新次數
    },
    mounted() {
        this.loginStatus(); // 確認登入狀態
        this.edit(); // 編輯個資
    },
    methods: {
        // 確認登入狀態
        loginStatus() {
            let formData = new FormData();
            formData.append('type', 'profile');
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
                    // 判斷折扣碼網址
                    const coupon = new URL(location.href).searchParams.get('coupon');
                    let couponUrl = '';
                    if (coupon) { couponUrl = '?coupon=' + coupon; };
                    // 登入中
                    if (data.result === 'success') {
                        this.emailCheck = false;
                        this.login = true; // 登入狀態
                        this.memberCode = data.member_code; // 會員編碼
                        this.memberId = data.member_code;
                        this.name = data.name;
                        this.phone = data.phone_number;
                        this.phonePass = data.sms_pass;
                        this.email = data.email;
                        this.emailPass = data.email_pass;
                        this.esliteCounts = data.eslite_update;
                        // 若有誠品卡號
                        if (data.eslite_code) {
                            this.esliteCode = data.eslite_code;
                        };
                        // 渲染 header
                        for (let i = 0; i < headerMenu.length; i++) {
                            headerMenu[i].innerHTML =
                                `<ul>
                                            <li class="d-flex justify-content-end align-items-center">
                                                <div class="event" style="cursor: pointer;">票匣</div>
                                                <img class="logout ms-3" style="cursor: pointer;" src="./img/logout.png" alt="logout" width="20" height="20">
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
                        // 前往活動頁
                        for (let i = 0; i < document.querySelectorAll('.event').length; i++) {
                            document.querySelectorAll('.event')[i].addEventListener('click', () => {
                                location.replace('./event.php' + couponUrl);
                            });
                        };
                    } else {
                        this.login = false;
                        location.replace('./index.php' + couponUrl);
                    };
                    this.load();
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 初始化
        reset() {
            let profileInput = document.querySelectorAll('.profile_input');
            for (let i = 0; i < profileInput.length; i++) {
                profileInput[i].classList.remove('profile_input-focus');
                document.querySelectorAll('.profile_edit')[i].classList.remove('d-none');
                document.querySelectorAll('.profile_save')[i].classList.add('d-none');
            };
            document.getElementById('eslite_notice').classList.add('d-none');
            this.phoneCode = '';
            this.emailCode = '';
        },
        // 編輯個資
        edit() {
            let profileInput = document.querySelectorAll('.profile_input');
            // 更新
            function save(type) {
                let formData = new FormData();
                formData.append('type', 'profile_update');
                formData.append('detail', type);
                formData.append('name', app.name);
                formData.append('phone', app.phone);
                formData.append('email', app.email);
                formData.append('esliteCode', app.esliteCode);
                axios({
                    method: 'post',
                    url: './api/member.php',
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                    .then((response) => {
                        let data = response.data;
                        console.log(data);
                        app.loginStatus();
                        alert(data.message);
                    })
                    .catch((response) => {
                        console.log(response);
                    });
            };
            // 點擊監聽
            document.querySelector('body').addEventListener('click', (event) => {
                // 
                if (event.target.dataset.edit) {
                    this.reset();
                    if (event.target.dataset.edit === 'name') {
                        profileInput[0].classList.add('profile_input-focus');
                        profileInput[0].focus();
                        document.querySelectorAll('.profile_save')[0].classList.remove('d-none');
                        document.querySelectorAll('.profile_edit')[0].classList.add('d-none');
                    };
                    if (event.target.dataset.edit === 'phone') {
                        if (this.phonePass == 'true') {
                            return alert('不可更改已驗證的行動電話');
                        };
                        profileInput[1].classList.add('profile_input-focus');
                        profileInput[1].focus();
                        document.querySelectorAll('.profile_save')[1].classList.remove('d-none');
                        document.querySelectorAll('.profile_edit')[1].classList.add('d-none');
                    };
                    if (event.target.dataset.edit === 'email') {
                        if (this.emailPass == 'true') {
                            return alert('不可更改已驗證的電子郵件');
                        };
                        profileInput[2].classList.add('profile_input-focus');
                        profileInput[2].focus();
                        document.querySelectorAll('.profile_save')[2].classList.remove('d-none');
                        document.querySelectorAll('.profile_edit')[2].classList.add('d-none');
                    };
                    if (event.target.dataset.edit === 'eslite') {
                        if (this.esliteCounts != 0) {
                            return alert('已超過可更新次數');
                        };
                        document.getElementById('eslite_notice').classList.remove('d-none');
                        profileInput[3].classList.add('profile_input-focus');
                        profileInput[3].focus();
                        document.querySelectorAll('.profile_save')[3].classList.remove('d-none');
                        document.querySelectorAll('.profile_edit')[3].classList.add('d-none');
                    };
                } else if (event.target.dataset.save) {
                    this.reset();
                    save(event.target.dataset.save);
                } else if (event.target.dataset.pass) {
                    return null;
                } else {
                    this.reset();
                    this.getMember();
                };

            });
        },
        // 送出手機、信箱驗證碼
        sendCode(type) {
            if (this.phone == '' && this.email == '') { return alert('錯誤'); };
            // call API
            let formData = new FormData();
            formData.append('type', 'send_code');
            formData.append('detail', type);
            formData.append('phone', this.phone);
            formData.append('email', this.email);
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 驗證手機、信箱
        checkCode(type) {
            if (this.phoneCode == '' && this.emailCode == '') { return alert('請輸入驗證碼'); };
            // call API
            let formData = new FormData();
            formData.append('type', 'check_code');
            formData.append('detail', type);
            formData.append('phone', this.phone);
            formData.append('phoneCode', this.phoneCode);
            formData.append('email', this.email);
            formData.append('emailCode', this.emailCode);
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                    alert(data.message);
                    if (data.result === 'fail') {
                        this.phoneCode = '';
                        this.emailCode = '';
                        return
                    };
                    this.getMember();
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 更新誠品卡號
        esliteUpdate() {
            // 更新誠品卡號
            let formData = new FormData();
            formData.append('type', 'update_eslite');
            formData.append('memberCode', this.memberCode);
            formData.append('esliteCode', document.getElementById('eslite_code').value);
            axios({
                method: 'post',
                url: './api/member.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                    if (data.result === 'fail') { return alert(data.message); };
                    alert(data.message);
                    location.reload();
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 初始化表單
        getMember() {
            let formData = new FormData();
            formData.append('type', 'profile');
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
                        this.emailCheck = false;
                        this.phoneCheck = false;
                        this.login = true; // 登入狀態
                        this.memberCode = data.member_code; // 會員編碼
                        this.memberId = data.member_code;
                        this.name = data.name;
                        this.phone = data.phone_number;
                        this.phonePass = data.sms_pass;
                        this.email = data.email;
                        this.emailPass = data.email_pass;
                        this.esliteCode = data.eslite_code;
                        this.esliteCounts = data.eslite_update;
                    } else {
                        location.replace('./index.php');
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 載入後
        load() {
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
                // document.querySelector('.ticket_box').click();
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