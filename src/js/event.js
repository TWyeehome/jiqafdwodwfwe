import Vue from 'vue'; // 引入 vue
import axios from 'axios';
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js'); // 引入 bootstrap 

const app = new Vue({
    el: '#vueApp',
    data: {
        memberId: 0, // 會員 id
        memberCode: 0, // 會員編碼
        esliteCode: '', // 誠品卡號
        memberQr: 0, // 會員 qrcode 
        memberPhone: 0, // 會員電話
        phoneTransfer: '', // 轉讓電話
        ticketTransfer: 1, // 轉讓張數
        getTotal: 1, // 持有票數
        getLeft: 0, // 剩餘票數
        ticketIndex: 0, // 票索引
        ticketName: '', // 票序號
        dayOff: '', // 日期倒數
        boughtReceipt: '', // 買過的明細
        ticketStatus: '載入中', // 票務狀態
        ticketType: '', // 票務類型
        ticketFilter: 'all',
        ticketTotal: 0,
        ticketTest: 0, // 測試票
        ticketDiscount: 0, // 超級早鳥票
        ticketEarly: 0, // 早鳥票
        ticketNormal: 0, // 全票
        ticketFamily: 0, // 親子套票
        /* 已兌換 */
        ticketTestUsed: 0, // 測試票
        ticketDiscountUsed: 0, // 超級早鳥票
        ticketEarlyUsed: 0, // 早鳥票
        ticketNormalUsed: 0, // 全票
        ticketFamilyUsed: 0, // 親子套票
        /* 尚未兌換 */
        ticketTestUnused: 0, // 測試票
        ticketDiscountUnused: 0, // 超級早鳥票
        ticketEarlyUnused: 0, // 早鳥票
        ticketNormalUnused: 0, // 全票
        ticketFamilyUnused: 0, // 親子套票
        // 誠品新規則
        giftCode: '',
    },
    mounted() {
        this.loginStatus(); // 確認登入狀態
        // this.dateCountdown(); // 活動開始日倒數計時
        this.invoice();
        this.gift(); // 兌換禮品
    },
    methods: {
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
                    // 判斷折扣碼網址
                    const coupon = new URL(location.href).searchParams.get('coupon');
                    let couponUrl = '';
                    if (coupon) { couponUrl = '?coupon=' + coupon; };
                    // 登入中
                    if (data.result === 'success') {
                        this.memberId = data.id; // 會員 id
                        this.payCheck(); // 確認訂單狀態
                        this.issuedCheck(); // 確認發行狀態
                        this.memberCode = data.member_code; // 會員編碼
                        this.memberQr = data.qr_prove; // 會員編碼
                        this.memberPhone = data.phone_number; // 會員電話
                        this.ticketList(data.member_code); // 票券狀態
                        this.ticketAct(); // 選票行為
                        // 若有誠品卡號
                        if (data.eslite_code) { this.esliteCode = data.eslite_code; };
                        for (let i = 0; i < headerMenu.length; i++) {
                            headerMenu[i].innerHTML =
                                `<ul>
                                    <li class="d-flex justify-content-end align-items-center">
                                        <div class="event" style="cursor: pointer;">HOME</div>
                                        <div class="menu_toggle dropstart">
                                            <img class="ms-3" style="cursor: pointer;" src="./img/menu.png" alt="logout" width="20" height="20" id="menu_` + i + `" data-bs-toggle="dropdown" aria-expanded="false">
                                            <ul class="dropdown-menu" aria-labelledby="menu_` + i + `">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center justify-content-end" href="./profile.php` + couponUrl + `">
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
                                    </li>` +
                                `</ul>`;
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
                                location.replace('./index.php' + couponUrl);
                            });
                        };
                    } else {
                        location.replace('./index.php');
                    };
                    this.load(); // 畫面渲染
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        // 票券狀態
        ticketCheck() {
            if (this.ticketTotal != 0) {
                this.qrcode(this.ticketIndex);
                setTimeout(() => { this.ticketCheck(); }, 3500);
            };
        },
        gift() {
            // 兌換視窗
            this.modalGift = new bootstrap.Modal(document.getElementById('modal_gift'), { keyboard: false });
            // 檢查
            document.querySelector('body').addEventListener('click', (event) => {
                if (event.target.dataset.gift) {
                    let formData = new FormData();
                    formData.append('type', 'check');
                    formData.append('code', event.target.dataset.gift); // 票券序號
                    axios({
                        method: 'post',
                        url: './api/gift.php',
                        data: formData,
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                        .then((response) => {
                            let data = response.data;
                            console.log(data);
                            if (data.result === 'success') {
                                if (data.gift == '') {
                                    document.getElementById('gift_exchange').textContent = '立即兌換';
                                } else {
                                    document.getElementById('gift_exchange').textContent = '贈品已兌換 ' + data.gift;
                                };
                                this.giftCode = event.target.dataset.gift;
                                this.modalGift.show();
                                return
                            };
                            alert(data.message);
                        })
                        .catch((response) => {
                            console.log(response);
                        });
                };
            });
            // 兌換禮品
            document.getElementById('gift_exchange').addEventListener('click', () => {
                let formData = new FormData();
                formData.append('type', 'exchange');
                formData.append('code', this.giftCode); // 票券序號
                axios({
                    method: 'post',
                    url: './api/gift.php',
                    data: formData,
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                    .then((response) => {
                        let data = response.data;
                        console.log(data);
                        if (data.result === 'fail') {
                            alert(data.message);
                            return
                        };
                        document.getElementById('gift_exchange').textContent = '贈品已兌換 ' + data.gift;
                    })
                    .catch((response) => {
                        console.log(response);
                    });
            });
        },
        // 確認發行狀態
        issuedCheck() {
            let formData = new FormData();
            formData.append('type', 'issued_check');
            axios({
                method: 'post',
                url: './api/ticket.php',
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
        // 確認訂單狀態
        payCheck() {
            let formData = new FormData();
            formData.append('type', 'pay_check');
            formData.append('memberId', this.memberId);
            axios({
                method: 'post',
                url: './api/ticket.php',
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
        // invoice
        invoice() {
            document.querySelector('body').addEventListener('click', (event) => {
                if (event.target.classList.contains('model_receipt') || event.target.classList.contains('ticket_open')) {
                    return
                };
                // 票務
                if (event.target.dataset.receipt) {
                    let formData = new FormData();
                    formData.append('type', 'get_ticket');
                    formData.append('ticketType', event.target.dataset.receipt);
                    formData.append('memberCode', this.memberCode);
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
                                let content = '';
                                let ticketTpye = '';
                                for (let i = 0; i < data.content.length; i++) {
                                    if (event.target.dataset.receipt == 'ticket_test') { ticketTpye = '測試票' };
                                    if (event.target.dataset.receipt == 'ticket_discount') { ticketTpye = '超級早鳥票' };
                                    if (event.target.dataset.receipt == 'ticket_early') { ticketTpye = '早鳥票' };
                                    if (event.target.dataset.receipt == 'ticket_normal') { ticketTpye = '全票' };
                                    if (event.target.dataset.receipt == 'ticket_family') { ticketTpye = '親子套票' };
                                    let ticketCode = '';
                                    for (let x = 0; x < data.content[i].length; x++) {
                                        ticketCode += `<p class="ticket_open ticket_content p-0">#` + data.content[i][x].ticket_code + `</p>`;
                                    };
                                    content +=
                                        `<hr>` +
                                        `<div style="margin-bottom: 16px"> 
                                            <p class="ticket_open ticket_content p-0">` + ticketTpye + `</p>
                                            <p class="ticket_open ticket_content p-0">` + data.content[i][0].pay_time + `</p>
                                            <p class="ticket_open ticket_content p-0">購入 ` + data.content[i].length + ` 張</p>
                                            ` + ticketCode + `
                                        </div>`;
                                };
                                document.getElementById('receipt').innerHTML = content;
                                document.querySelector('.model_receipt').classList.remove('d-none');
                                return
                            };
                            alert(data.message);
                        })
                        .catch((response) => {
                            console.log(response);
                        });
                    return
                };
                // 發票
                if (event.target.dataset.invoice) {
                    let formData = new FormData();
                    formData.append('type', 'get_invoice');
                    formData.append('memberCode', this.memberCode);
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
                                let content = '';
                                let ticketTpye = '';
                                for (let i = 0; i < data.invoice.length; i++) {
                                    if (data.invoice[i].ticket_type == event.target.dataset.receipt) {
                                        if (data.invoice[i].ticket_type == 'ticket_discount') { ticketTpye = '超級早鳥票' };
                                        if (data.invoice[i].ticket_type == 'ticket_early') { ticketTpye = '早鳥票' };
                                        if (data.invoice[i].ticket_type == 'ticket_normal') { ticketTpye = '全票' };
                                        if (data.invoice[i].ticket_type == 'ticket_family') { ticketTpye = '親子套票' };
                                        // 折扣碼
                                        let couponDetail = '';
                                        if (data.invoice[i].coupon) {
                                            couponDetail = `<p class="ticket_open ticket_content p-0">折扣碼: ` + data.invoice[i].coupon + `</p>`;
                                        };
                                        // 公司抬頭與統一編號
                                        let companyDetail = '';
                                        if (data.invoice[i].company_id) {
                                            companyDetail =
                                                `<p class="ticket_open ticket_content p-0">公司抬頭: ` + data.invoice[i].company_name + `</p>` +
                                                `<p class="ticket_open ticket_content p-0">統一編號: ` + data.invoice[i].company_id + `</p>`
                                                ;
                                        };
                                        // 誠品會員
                                        let esliteCode = '';
                                        if (data.invoice[i].eslite_code) {
                                            esliteCode = `<p class="ticket_open ticket_content p-0">誠品會員卡號: ` + data.invoice[i].eslite_code + `</p>`;
                                        };
                                        // 渲染
                                        content +=
                                            `<hr class="ticket_open">` +
                                            `<div class="ticket_open" style="margin-bottom: 16px">
                                                <p class="ticket_open ticket_content p-0">` + data.invoice[i].pay_time + `</p>` +
                                            // <p class="ticket_open ticket_content p-0">測試票: 20元*` + data.invoice[i].ticket_test + `張</p>
                                            `<p class="ticket_open ticket_content p-0">超級早鳥票: 300元*` + data.invoice[i].ticket_discount + `張</p>
                                                <p class="ticket_open ticket_content p-0">早鳥票: 300元*` + data.invoice[i].ticket_early + `張</p> 
                                                <p class="ticket_open ticket_content p-0">全票: 360元*` + data.invoice[i].ticket_normal + `張</p> 
                                                <p class="ticket_open ticket_content p-0">親子套票: 600元*` + data.invoice[i].ticket_family + `張</p>
                                                ` + couponDetail + `
                                                ` + companyDetail + `
                                                <p class="ticket_open ticket_content p-0">付款金額: ` + data.invoice[i].price + `元</p>
                                                <p class="ticket_open ticket_content p-0">訂單編號: ` + data.invoice[i].order_code + `</p>
                                                ` + esliteCode + `
                                            </div>`
                                    };
                                };
                                document.getElementById('receipt').innerHTML = content;
                                document.querySelector('.model_receipt').classList.remove('d-none');
                                return
                            };
                            alert(data.message);
                        })
                        .catch((response) => {
                            console.log(response);
                        });
                    return
                };
                document.querySelector('.model_receipt').classList.add('d-none');
            });
        },
        // 倒數計時
        dateCountdown() {
            // 2023/04/07
            const countDownDate = new Date('April 7, 2024 20:00:00').getTime();
            let now = new Date().getTime();
            let distance = countDownDate - now;
            let days = Math.floor(distance / (1000 * 60 * 60 * 24));
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.dayOff = days + 'D ' + hours + 'H' + ' to APR. 07' + ' 20:00 (8PM)'
        },
        // 登入
        login() {
            location.replace('./prove_phone.php');
        },
        ticketList(memberCode) {
            let formData = new FormData();
            formData.append('type', 'get');
            formData.append('memberCode', memberCode);
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
                        if (data.content.length) {
                            this.qrcode(this.ticketIndex);
                        } else {
                            this.ticketStatus = '無票券';
                            document.getElementById('ticket_gift').textContent = '無贈品';
                        };
                        this.ticketTotal = data.ticket_total;
                        this.ticketCheck(); // 票券狀態
                        this.ticketDiscount = data.ticket_discount; // 超級早鳥票
                        this.ticketEarly = data.ticket_early; // 早鳥票
                        this.ticketNormal = data.ticket_normal; // 全票
                        this.ticketFamily = data.ticket_family; // 親子套票
                        /* 已兌換 */
                        this.ticketTestUsed = data.ticket_test.used;
                        this.ticketDiscountUsed = data.ticket_discount.used;
                        this.ticketEarlyUsed = data.ticket_early.used;
                        this.ticketNormalUsed = data.ticket_normal.used;
                        this.ticketFamilyUsed = data.ticket_family.used;
                        /* 尚未兌換 */
                        this.ticketTestUnused = data.ticket_test.unused;
                        this.ticketDiscountUnused = data.ticket_discount.unused;
                        this.ticketEarlyUnused = data.ticket_early.unused;
                        this.ticketNormalUnused = data.ticket_normal.unused;
                        this.ticketFamilyUnused = data.ticket_family.unused;
                        // 判斷是否有任何一張票已被使用
                        if (this.ticketTestUsed || this.ticketDiscountUsed || this.ticketEarlyUsed || this.ticketNormalUsed || this.ticketFamilyUsed) {
                            document.getElementById('manual').classList.remove('d-none');
                            document.getElementById('manual').classList.add('d-flex');
                            document.getElementById('manual').classList.add('justify-content-center');
                        };
                        return
                    };
                    alert(data.message);
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        qrcode(index) {
            document.getElementById('ticket_gift').style.visibility = 'hidden';
            // 
            let formData = new FormData();
            formData.append('type', 'get');
            formData.append('filter', this.ticketFilter);
            formData.append('memberCode', this.memberCode);
            axios({
                method: 'post',
                url: './api/ticket.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    let data = response.data;
                    console.log(data);
                    this.getLeft = data.content.length;
                    if (this.getLeft) {
                        // 票務類型
                        if (data.content[index].ticket_type == 'ticket_test') { app.ticketType = '測試票' };
                        if (data.content[index].ticket_type == 'ticket_discount') {
                            app.ticketType = '超級早鳥票';
                            document.getElementById('ticket_gift').style.visibility = 'visible';
                            document.getElementById('ticket_gift').dataset.gift = data.content[index].ticket_code;
                            if (data.content[index].gift) {
                                document.getElementById('ticket_gift').textContent = '贈品已兌換';
                                document.getElementById('ticket_gift').classList.remove('qr_result-gift');
                            } else {
                                document.getElementById('ticket_gift').textContent = '贈品未兌換';
                                document.getElementById('ticket_gift').classList.add('qr_result-gift');
                            };
                        };
                        if (data.content[index].ticket_type == 'ticket_early') { app.ticketType = '早鳥票' };
                        if (data.content[index].ticket_type == 'ticket_normal') { app.ticketType = '全票' };
                        if (data.content[index].ticket_type == 'ticket_family') { app.ticketType = '親子套票' };
                        if (data.content[index].used == 'true') {
                            document.querySelector('.qr_box').innerHTML =
                                `<p class="d-block" style="color: #0D0D10;">已使用<br>` + data.content[index].used_time + `</p>`
                                ;
                            document.querySelector('.qr_result').classList.add('qr_result-used');
                            // 票名 若使用過
                            app.ticketStatus = '已使用 ' + data.content[index].used_time;
                            app.ticketName = data.content[index].ticket_code;
                        } else {
                            document.querySelector('.qr_result').classList.remove('qr_result-used');
                            // 票名 未使用過
                            app.ticketStatus = '尚未兌換';
                            app.ticketName = data.content[index].ticket_code;
                            // console.log(response.data);
                            document.querySelector('.qr_box').innerHTML = '';
                            QRCode(document.querySelector('.qr_box'), {
                                // member_id, ticket_id
                                // location.href + 
                                text: 't=' + data.content[index].ticket_code,
                                width: '236',
                                height: '236',
                                correctLevel: QRCode.CorrectLevel.H
                            });
                        };

                    } else {
                        document.querySelector('.qr_result').classList.remove('qr_result-used');
                        document.querySelector('.qr_box').innerHTML = '<a href="./index.php" class="d-block center" style="color: #0D0D10;">購票去</a>';
                        this.ticketType = '';
                        this.ticketName = '';
                        this.ticketStatus = '查無票券';
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
        ticketAct() {
            // 上個 qrcode
            document.getElementById('previous').addEventListener('click', () => {
                console.log(this.ticketIndex);
                this.ticketIndex -= 1;
                if (this.ticketIndex < 0) {
                    this.ticketIndex = 0;
                    return
                };
                this.qrcode(this.ticketIndex);
            });
            // 下個 qrcode
            document.getElementById('next').addEventListener('click', () => {
                console.log(this.ticketIndex);
                if (this.ticketIndex < this.getLeft - 1) {
                    this.ticketIndex += 1;
                    this.qrcode(this.ticketIndex);
                };
            });
        },
        // 載入後
        load() {
            document.getElementById('mainpage').classList.add('mainpage--pass');
            // 是否為行動裝置 
            const mobileDevices = ['Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone', 'iPhone Simulator', 'iPhone Simulator', 'iPod Simulator', 'iPad Simulator', 'Pike v7.6 release 92', 'Pike v7.8 release 517']
            this.isMobileDevice = false;
            for (let i = 0; i < mobileDevices.length; i++) {
                if (navigator.userAgent.match(mobileDevices[i])) this.isMobileDevice = true;
            };
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
    }
});