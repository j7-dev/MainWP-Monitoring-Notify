<?php
declare (strict_types = 1);

namespace J7\MainWP_Monitoring_Notify_Extension;

class Settings
{
    public static function render_tabs()
    {
        $tabs = [
            'monitoring-notify-settings' => [
                'label'    => 'Settings',
                'callback' => 'render_form',
                'icon'     => 'cog',
             ],
            'monitoring-notify-crontab'  => [
                'label'    => 'Crontab',
                'callback' => 'render_crontab',
                'icon'     => 'stopwatch',
             ],
            'monitoring-notify-about'    => [
                'label'    => 'About',
                'callback' => 'render_about',
                'icon'     => 'exclamation circle',
             ],
         ]

        ?>
<div class="ui labeled icon inverted menu mainwp-sub-submenu">
	<?php foreach ($tabs as $key => $tab):
            $active = $key === 'monitoring-notify-settings' ? 'active' : '';
            ?>
		<a href="#" class="item <?=$active?>" data-tab="<?=$key?>"><i
				class="<?=$tab[ 'icon' ]?> icon"></i> <?=$tab[ 'label' ]?></a>
		<?php endforeach;?>
</div>

<?php foreach ($tabs as $key => $tab):
            $active = $key === 'monitoring-notify-settings' ? 'active' : '';
            ?>
	<div class="ui tab segment <?=$active?>" data-tab="<?=$key?>">
		<?php call_user_func([ __NAMESPACE__ . '\Settings', $tab[ 'callback' ] ])?>
	</div>
	<?php endforeach;?>
<script>
(function($) {
	$('.menu .item').tab();
})(jQuery)
</script>

<?php

    }

    public static function render_about()
    {
        $questions = [
            [
                'q' => '多久檢查一次?',
                'a' => '看你 <code>Sites > Monitoring</code> ，中的設置是多久，最短可以 <code>每 5 分鐘</code> 檢查一次',
             ],
            [
                'q' => '運作原理是什麼?',
                'a' => '是使用 <code>WP CRON</code> 搭配 <code>MainWP</code> 本身的 hook 做成<br /><br />
				⚠️ <code>WP CRON</code> 必須是有人造訪網站時才會觸發，如果您的網站流量本身並不高，推薦使用 主機本身提供的 <code>crontab</code> 來實現，詳細可參考 <a href="https://kb.mainwp.com/disable-wp-cron/" target="_blank">官方文章</a> 或 <a href="https://studiofreya.com/2016/01/10/how-to-trigger-wp-cron-from-crontab-in-wordpress/" target="_blank">這篇文章</a><br /><br />
				也因為如此，斷線的檢查推波通知 <code>並非準確的5分鐘</code>
				',
             ],
         ];

        $records = [
            [
                'key'   => '套件名稱',
                'value' => 'MainWP Monitoring Notify Extension',
             ],
            [
                'key'   => '版本號',
                'value' => Utils::get_plugin_ver(),
             ],
            [
                'key'   => '開發者',
                'value' => '<a href="https://github.com/j7-dev" target="_black">J7 <i class="github icon"></i></a> ',
             ],
            [
                'key'   => '程式碼倉庫',
                'value' => '<a href="https://github.com/j7-dev/MainWP-Monitoring-Notify" target="_black"><div class="ui labeled button" tabindex="0">
				<div class="ui basic blue button">
				<i class="github icon"></i> GitHub
				</div>
				<span class="ui basic left pointing blue label">
				⭐
				</span>
			</div></a><span style="margin-left:2rem;">您的星星是給開發者的肯定</span>',
             ],
            [
                'key'   => '開源贊助',
                'value' => '<a href="#" target="_black"><button class="ui blue button"><i class="coffee icon" style="color:#fff !important;"></i> 請我喝杯咖啡</button>
			</a>',
             ],
            [
                'key'   => 'Bug 回報',
                'value' => '<a href="https://github.com/j7-dev/MainWP-Monitoring-Notify/issues" target="_black"><button class="ui green button"><i class="bug icon" style="color:#fff !important;"></i> GitHub Issues</button>
			</a>',
             ],
         ]
        ?>
<div class="ui grid field">
	<div class="eight wide column">
		<?php self::renderTable($records);?>
	</div>
</div>
<?php
}

    public static function render_crontab()
    {
        $base_url             = Utils::get_plugin_url();
        $monitoring_sites_url = add_query_arg(array(
            'page' => 'MonitoringSites',
        ), admin_url('admin.php'));
        $questions = [
            [
                'q' => '多久檢查一次?',
                'a' => "看你 <code><a href='{$monitoring_sites_url}' target='_blank'>Sites > Monitoring</a></code> ，中的設置是多久，最短可以 <code>每 5 分鐘</code> 檢查一次",
             ],
            [
                'q' => '運作原理是什麼?',
                'a' => '使用 <code>WP CRON</code> hook 做成<br /><br />
				⚠️ <code>WP CRON</code> 必須是<b style="color: var(--red-color)">有人造訪網站時才會觸發</b>，如果您的網站流量本身並不高，推薦使用 主機本身提供的 <code>crontab</code> 來實現，詳細可參考 <a href="https://kb.mainwp.com/disable-wp-cron/" target="_blank">官方文章</a> 或 <a href="https://studiofreya.com/2016/01/10/how-to-trigger-wp-cron-from-crontab-in-wordpress/" target="_blank">這篇文章</a><br /><br />
				也因為如此，斷線的檢查推波通知 <b style="color: var(--red-color)">並非準確的 5 分鐘</b><br /><br />
				每個站取得狀態後會暫停 0.1 秒，避免過多站台造成伺服器負擔<br />
				',
             ],
            [
                'q' => '為什麼通知的網站都是 <無法取得 http 狀態碼> ?',
                'a' => "獲取狀態是使用 php 的 cURL 方法去獲取的，請確認你的伺服器可以使用此方法",
             ],
            [
                'q' => '[進階] 設定準確的定時任務 - 使用 crontab',
                'a' => "如果你的主機商允許你設定 <code>crontab</code> 那麼，你可以透過 crontab 實現準確的定時任務，也可以參考此 <a href='https://linuxhandbook.com/crontab/' target='_blank'>crontab 教學</a><br /><br />
				<ol>
					<li>
						<p>取消這個外掛的 <code>WP CRON</code> </p>
						<p>在你主題底下的 functions.php 輸入 </p>
						<p>
						<code>
						add_filter( 'mainwp_monitoring_notify_wp_cron_enabled' , '__return_false', 100, 1 );
						</code>
						</p><br /><br />
					</li>
					<li>
						<p>ssh 伺服器後輸入 <code>crontab -e</code> ，開啟 <code>crontab</code> 設定</p>
						<p>⚠️ 請確保你的伺服器上可以執行 <code>php</code> 指令</p>
						<p>⚠️ 下方的 <code>{{PATH}}</code> 請自行替換此網站的檔案路徑，例: <code>/var/www/html/MY_SITE</code></p>
						<p>例: 每分鐘執行一次:</p>
						<p><code>* * * * * php <span style='color: var(--red-color)'>{{PATH}}</span>/wp-content/plugins/mainwp-monitoring-notify/cron/exec.php > /dev/null 2>&1</code></p>
						<p>例: 每 <span style='color: var(--red-color)'>5</span> 分鐘執行一次:</p>
						<p><code>*<span style='color: var(--red-color)'>/5</span> * * * * php <span style='color: var(--red-color)'>{{PATH}}</span>/wp-content/plugins/mainwp-monitoring-notify/cron/exec.php > /dev/null 2>&1</code></p><br /><br />
					</li>

					<li>
						<p>範例: </p>
						<p><a href='{$base_url}/assets/image/crontab.png' target='_blank'><img style='width:10rem;' src='{$base_url}/assets/image/crontab.png' /></a></p>
					</li>
				</ol>
				",
             ],
         ];

        ?>
<div class="ui grid field">
	<div class="wide column">
		<?php self::renderQA($questions);?>
	</div>

</div>
<?php
}

    public static function render_form()
    {
        ?>
<div class="ui segment">
	<form method="post" enctype="multipart/form-data" id="mainwp-monitoring-notify-settings-page-form"
		class="ui form">
		<?php self::render_fields();?>
		<div class="mainwp-form-footer">
			<div class="ui divider"></div>
			<button id="monitoring_notify_submit_btn"
				class="ui big green button"><?php _e('Save Settings', 'mainwp-monitoring-notify-extension');?></button>
			<button type="button" id="monitoring_notify_run_test" class="ui big button"
				style="margin-left:1rem !important;"><?php _e('Run Test', 'mainwp-monitoring-notify-extension');?></button>
		</div>
		<div id="response_msg" style="margin-top:2rem;"></div>
	</form>
</div>

<script>
(function($) {
	$('#monitoring_notify_run_test').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#response_msg').html(
			'<div class="ui active inline loader"></div><span style="margin-left:1rem;">正在執行檢查...，如果你的網站很多，請多給它一點時間🙏，如果你確定請求已經發出，先關閉視窗也沒關係</span>'
		);
		const data = {
			'action': 'run_test',
		};
		$.post("<?=admin_url('admin-post.php')?>", data, function(response) {
			alert('已執行檢查，並發送 LINE 通知');
			$('#response_msg').html('');
		});
	})

})(jQuery)
</script>
<?php
}

    public static function render_fields()
    {
        $line_token                    = \get_option(Bootstrap::LINE_TOKEN_FIELD_NAME, '');
        $only_notify_when_site_offline = (bool) \get_option(Bootstrap::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME, '0');
        $interval_in_minute            = \get_option(Bootstrap::INTERVAL_IN_MINUTE_FIELD_NAME, '5');
        $hide_healthy_sites            = (bool) \get_option(Bootstrap::HIDE_HEALTHY_SITES_FIELD_NAME, '0');
        $show_system_info              = (bool) \get_option(Bootstrap::SHOW_SYSTEM_INFO_FIELD_NAME, '0');
        $base_url                      = Utils::get_plugin_url();
        $helpers                       = [
            [
                'content' => '請先前往 <a href="https://notify-bot.line.me/zh_TW/" target="_blank">LINE Notify</a> 並登入你的 LINE',
                'image'   => '',
             ],
            [
                'content' => '點擊 <code>Generate token</code> 然後選擇想要通知的聊天室',
                'image'   => "{$base_url}/assets/image/choose_chatroom.png",
             ],
            [
                'content' => '複製 <code>token</code> 然後貼到這邊保存',
                'image'   => "{$base_url}/assets/image/get_token.png",
             ],
            [
                'content' => '最後，邀請 LINE Notify 到你<code>第二步選擇的聊天室</code>就完成了🎉🎉🎉',
                'image'   => "{$base_url}/assets/image/invite.png",
             ],
         ];
        $modal_props = [
            'key'           => 'tutorial',
            'label'         => '<i class="info circle icon"></i> 教學',
            'title'         => '如何申請 LINE Notify Token',
            'content'       => [ __NAMESPACE__ . '\Settings', 'renderList' ],
            'content_props' => $helpers,
         ];

        ?>
<div class="ui grid field">
	<label
		class="six wide column middle aligned"><?php \_e('Line Notify Token', 'mainwp-monitoring-notify-extension');?><span
			style="margin-right:1rem;"></span><?php self::renderModal($modal_props)?></label>
	<div class="ten wide column">
		<input type="text" name="<?=Bootstrap::LINE_TOKEN_FIELD_NAME?>"
			id="<?=Bootstrap::LINE_TOKEN_FIELD_NAME?>" value="<?=$line_token?>" />
	</div>
</div>

<div class="ui grid field">
	<label
		class="six wide column middle aligned"><?php \_e('多久通知一次(分鐘)', 'mainwp-monitoring-notify-extension');?><span
			style="margin-right:1rem;"></span></label>
	<div class="ten wide column">
		<input type="number" min="1" name="<?=Bootstrap::INTERVAL_IN_MINUTE_FIELD_NAME?>"
			id="<?=Bootstrap::INTERVAL_IN_MINUTE_FIELD_NAME?>" value="<?=$interval_in_minute?>" />
	</div>
</div>

<div class="ui grid field">
	<label class="six wide column middle aligned" data-inverted=""
		data-position="top left"><?php \_e('只有網站斷線時才發通知', 'mainwp-monitoring-notify-extension');?></label>
	<div class="ten wide column">
		<input type="checkbox" name="<?=Bootstrap::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME?>"
			id="<?=Bootstrap::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME?>"
			style="position: relative;top: 7px;" <?php \checked($only_notify_when_site_offline);?> />
	</div>
</div>

<div class="ui grid field">
	<label class="six wide column middle aligned" data-inverted=""
		data-position="top left"><?php _e('隱藏狀態正常的網站', 'mainwp-monitoring-notify-extension');?></label>
	<div class="ten wide column">
		<input type="checkbox" name="<?=Bootstrap::HIDE_HEALTHY_SITES_FIELD_NAME?>"
			id="<?=Bootstrap::HIDE_HEALTHY_SITES_FIELD_NAME?>" style="position: relative;top: 7px;"
			<?php checked($hide_healthy_sites);?> /> <span>如果網站的 <code>http code</code> 是
			<code>2XX</code> 或 <code>3XX</code> 就不會通知</span>
	</div>
</div>

<div class="ui grid field">
	<label class="six wide column middle aligned" data-inverted=""
		data-position="top left"><?php _e('顯示系統資訊', 'mainwp-monitoring-notify-extension');?></label>
	<div class="ten wide column">
		<input type="checkbox" name="<?=Bootstrap::SHOW_SYSTEM_INFO_FIELD_NAME?>"
			id="<?=Bootstrap::SHOW_SYSTEM_INFO_FIELD_NAME?>" style="position: relative;top: 7px;"
			<?php checked($show_system_info);?> /> <span>顯示你的 MainWP Dashboard 伺服器的 <code>cpu</code>,
			<code>ram</code>, <code>average load</code>, <code>nginx</code>, <code>MySQL</code>
			的資訊和狀態</span>
	</div>
</div>

<?php

    }

    public static function renderList($helpers)
    {
        ?>
<ol>
	<?php foreach ($helpers as $key => $helper): ?>
	<li style="color: #666;">
		<p><?=$helper[ 'content' ]?></p>
		<?php if (!empty($helper[ 'image' ])): ?>
		<p><a href="<?=$helper[ 'image' ]?>" target="_blank"><img style="width:10rem;"
					src="<?=$helper[ 'image' ]?>" /></a></p>
		<?php endif;?>
	</li>
	<?php endforeach;?>
</ol>
<?php
}

    public static function renderQA($questions)
    {
        foreach ($questions as $key => $question): ?>
<div class="ui icon info message">

	<div class="content">
		<div class="header" style="margin-bottom:2rem;">
			<?=$question[ 'q' ]?>
		</div>
		<p><?=$question[ 'a' ]?></p>
	</div>
</div>
<?php endforeach;
    }

    public static function renderModal($modal_props)
    {
        $default_modal_props = [
            'key'           => wp_unique_id(),
            'label'         => '開啟 Modal',
            'title'         => 'Modal Title',
            'content'       => [  ],
            'content_props' => [  ],
         ];
        $modal_props = array_merge($default_modal_props, $modal_props);
        ?>
<button data-modal="<?=$modal_props[ 'key' ]?>" class="mini ui blue button">
	<?=$modal_props[ 'label' ]?>
</button>
<div id="<?=$modal_props[ 'key' ]?>" class="ui modal">
	<i class="close icon"></i>
	<div class="header">
		<?=$modal_props[ 'title' ]?>
	</div>
	<div class="content">
		<?php call_user_func_array($modal_props[ 'content' ], [ $modal_props[ 'content_props' ] ])?>
	</div>
</div>

<script>
(function($) {
	const btn = $('button[data-modal="<?=$modal_props[ 'key' ]?>"]');
	btn.click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#<?=$modal_props[ 'key' ]?>').modal('show');
	})
})(jQuery)
</script>
<?php
}

    public static function renderTable($records)
    {
        ?>
<table class="ui celled table">
	<tbody>

		<?php foreach ($records as $record): ?>
		<tr>
			<td data-label="key"><?=$record[ 'key' ]?></td>
			<td data-label="value"><?=$record[ 'value' ]?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>

<?php
}
    public static function on_load_page()
    {
    }
}