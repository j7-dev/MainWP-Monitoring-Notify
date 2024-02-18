# MainWP Monitoring Notify | 讓 MainWP 監控更及時 :
一句話講完 MainWP Monitoring Notify :
> MainWP Monitoring Notify 是一個 WordPress 套件，依賴 MainWP 來監控網站運作狀態，並透過 LINE Notify 來通知你。


<br><br><br>

### 預覽

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/1e115794-eba1-40c4-8198-06f3eaf577f9)


<br><br><br>

## ⚡ 主要功能

##### 使用 LINE NOTIFY 通知

##### 如果你覺得通知太頻繁，可以設定，只有網站斷線時才通知

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/d438309e-0dbb-4e26-83b7-054f7be0df38)




<br><br><br>

## 1. 如何申請 LINE Notify Token

#### 1-1. 請先前往 LINE Notify 並登入你的 LINE

#### 1-2. 點擊 `Generate token` 然後選擇想要通知的聊天室

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/0341f0b5-b60f-4f51-9127-067753264472)

#### 1-3. 複製 `token` 然後貼到這邊保存

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/38e930ec-642e-4114-8137-8c54ad8ea7f9)

#### 1-4. 最後，邀請 LINE Notify 到你第二步選擇的聊天室就完成了🎉🎉🎉

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/4e000367-9c5f-46a4-8c4a-2ae61fb01dce)


<br><br><br>

## 2. 常見 Q & A 

#### 2-1. 多久檢查一次?

> 看你 `Sites > Monitoring` ，中的設置是多久，最短可以 每 5 分鐘 檢查一次

#### 2-2. 運作原理是什麼?

> 是使用 `WP CRON` 搭配 `MainWP` 本身的 `hook` 做成
> 
> ⚠️ `WP CRON` 必須是 有人造訪網站時才會觸發，如果您的網站流量本身並不高，推薦使用 主機本身提供的 `crontab` 來實現，詳細可參考 [官方文章](https://kb.mainwp.com/disable-wp-cron/) 或 [這篇文章](https://studiofreya.com/2016/01/10/how-to-trigger-wp-cron-from-crontab-in-wordpress/)
> 
> 也因為如此，斷線的檢查推波通知 並非準確的 5 分鐘

#### 2-3. [進階] 設定準確的定時任務 - 使用 crontab
>
> 如果你的主機商允許你設定 `crontab` 那麼，你可以透過 `crontab` 實現準確的定時任務，也可以參考此 [`crontab` 教學](https://linuxhandbook.com/crontab/)

> 2-3-1. 取消這個外掛的 `WP CRON`
> 
> 在你主題底下的 `functions.php` 輸入
> 
> `add_filter( 'mainwp_monitoring_notify_wp_cron_enabled' , '__return_false', 100, 1 );`


> 2-3-2. ssh 伺服器後輸入 `crontab -e` ，開啟 `crontab` 設定
> 
> ⚠️ 請確保你的伺服器上可以執行 php 指令
> 
> ⚠️ 下方的 `{{PATH}}` 請自行替換此網站的檔案路徑，例: `/var/www/html/MY_SITE`
> 
> 例: 每分鐘執行一次:
> 
> `* * * * * php {{PATH}}/wp-content/plugins/mainwp-monitoring-notify/cron/exec.php > /dev/null 2>&1`
> 
> 例: 每 5 分鐘執行一次:
> 
> `*/5 * * * * php {{PATH}}/wp-content/plugins/mainwp-monitoring-notify/cron/exec.php > /dev/null 2>&1`

![image](https://github.com/j7-dev/MainWP-Monitoring-Notify/assets/9213776/af553efa-a490-4e2c-8c41-89476057869b)

<br><br><br>
