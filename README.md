# MainWP Monitoring Notify | 讓 MainWP 監控更及時 :

> MainWP Monitoring Notify 是一個 WordPress 套件，依賴 MainWP 來監控網站運作狀態，並透過 LINE Notify 來通知你。


<br><br><br>

## 1. 取得 Line Notify Token

[教學]


新增一系列設定

<br><br><br>

## 2. 每個網站 template 都可以設定售價(未完成)

目前只有先做好欄位，功能尚未串接

<br><br><br>

## 3. 儲值後自動升為經銷商等級

在 `1.` 的設定中，可以設定，要讓經銷商購買什麼產品，才會升級為 `低階` | `高階` 經銷商

觸發條件目前設定為 當訂單 `已完成` 時，會觸發升級

降級則需要手動調整

經銷商編輯頁面則可以設定主機費的折扣 ↓

![image](https://github.com/j7-dev/wp-power-membership/assets/9213776/f8b6b826-ba5a-4c66-ac3b-bd765c23545a)

<br><br><br>

## 4. WP CRON 每日執行扣點

每天會把所有的經銷商抓出來扣點，如果點數不足，會發生什麼事情還沒有設定

<br><br><br>

## 5. 提供客製化 API

### 5.1. sync site (即複製模板網站) 到指定 Server

API endpoint: [POST] `{home_url}/wp-json/power-partner-server/site-sync`

body form data:
```
"site_id": 123, // 必填，要複製的網站 ID
"server_id": 456 // 選填，如果沒有指定，會從你的設定中隨機選擇一台 Server
"host_position": "jp" // 選填  tw | jp ，預設為 jp ，如果沒有填預設會開在日本主機
```

將會複製 `site_id` 的網站複製在允許的 Server 其中一台(隨機)

<br><br>

### 5.2. get template sites 取得所有模板網站

API endpoint: [GET] `{home_url}/wp-json/power-partner-server/get-template-sites`

<br><br>

### 5.3. get user id 用來讓 partner 登入後取得相關資訊

API endpoint: [POST] `{home_url}/wp-json/power-partner-server/get-user-id`

body form data:
```
email: "",
password: ""
```
<br><br><br>

---

## 關於名詞定義

經銷商的 user meta_key 是 `power_partner_server_partner_lv`

初階經銷商 - 1

高階經銷商 - 2

最低 priority 從1開始

每個 app 的 post_meta 都有個 `wpapp_site_status` 紀錄，如果值是 `off` 就是 disable
