
import Vue from 'vue'; // 引入 vue
import axios from 'axios';

const app = new Vue({
    el: '#vueapp',
    data: {
        tradeNo: '',
        title: 'LOADING...',
        buyStatus: 'a', // 購票結果
    },
    mounted() {
        this.check();
    },
    methods: {
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
        // 確認結帳結果
        check() {
            // 確認網址參數
            let url = new URL(location.href);
            this.tradeNo = url.searchParams.get('trade_no');
            console.log(this.tradeNo);
            if (!this.tradeNo) return this.title = '查無此訂單';
            let formData = new FormData();
            formData.append('tradeNo', this.tradeNo);
            axios({
                method: 'post',
                url: './api/sample_QueryTradeInfo.php',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            })
                .then((response) => {
                    console.log(response.data);
                    let data = response.data;
                    if (data.TradeStatus == 1) {
                        this.buyStatus = 'a';
                        this.title = '付款完成';
                        let content = '';
                        content =
                            // '訂單明細: ' + data.ItemName + '<br>' +
                            '付款金額: ' + data.TradeAmt + '元' + '<br>' +
                            '交易時間: ' + data.TradeDate + '<br>' +
                            '付款時間: ' + data.PaymentDate + '<br>' +
                            '訂單編號	: ' + data.MerchantTradeNo;
                        document.getElementById('result').innerHTML = content;
                        // 清除購買明細 cookie
                        document.cookie = 'ticketDiscount=0; max-age=0'; // 超級早鳥票
                        document.cookie = 'ticketEarly=0; max-age=0'; // 早鳥票
                        document.cookie = 'ticketNormal=0; max-age=0'; // 全票
                        document.cookie = 'ticketFamily=0; max-age=0'; // 親子套票
                        this.issuedCheck();
                        return
                    };
                    // 若刷卡失敗
                    this.buyStatus = 'b';
                    this.title = '付款失敗';
                    if (data.message) {
                        this.title = '查無此訂單';
                    } else {
                        document.getElementById('result').innerHTML = '<p class="text-center">' + '信用卡資訊不完整 / 錯誤 ，請重新確認。' + '</p>';
                    };
                })
                .catch((response) => {
                    console.log(response);
                });
        },
    }
});