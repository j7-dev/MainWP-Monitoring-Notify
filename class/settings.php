<?php

class MainWP_Monitoring_Notify_Settings
{
	public function __construct()
	{
	}

	public static function render_tabs()
	{
		$tabs = [
			'monitoring-notify-settings' => [
				'label' => 'Settings',
				'callback' => 'render_form',
				'icon' => 'cog',
			],
			'monitoring-notify-about' => [
				'label' => 'About',
				'callback' => 'render_about',
				'icon' => 'exclamation circle'
			]
		]


?>
		<div class="ui labeled icon inverted menu mainwp-sub-submenu">
			<?php foreach ($tabs as $key => $tab) :
				$active = $key === 'monitoring-notify-settings' ? 'active' : '';
			?>
				<a href="#" class="item <?= $active ?>" data-tab="<?= $key ?>"><i class="<?= $tab['icon'] ?> icon"></i> <?= $tab['label'] ?></a>
			<?php endforeach; ?>
		</div>

		<?php foreach ($tabs as $key => $tab) :
			$active = $key === 'monitoring-notify-settings' ? 'active' : '';
		?>
			<div class="ui tab segment <?= $active ?>" data-tab="<?= $key ?>">
				<?php call_user_func(['MainWP_Monitoring_Notify_Settings', $tab['callback']]) ?>
			</div>
		<?php endforeach; ?>
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
				'a' => '看你 <code>Sites > Monitoring</code> ，中的設置是多久，最短可以 <code>每 5 分鐘</code> 檢查一次'
			],
			[
				'q' => '運作原理是什麼?',
				'a' => '是使用 <code>WP CRON</code> 搭配 <code>MainWP</code> 本身的 hook 做成<br /><br />
				⚠️ <code>WP CRON</code> 必須是有人造訪網站時才會觸發，如果您的網站流量本身並不高，推薦使用 主機本身提供的 <code>crontab</code> 來實現，詳細可參考 <a href="https://kb.mainwp.com/disable-wp-cron/" target="_blank">官方文章</a> 或 <a href="https://studiofreya.com/2016/01/10/how-to-trigger-wp-cron-from-crontab-in-wordpress/" target="_blank">這篇文章</a><br /><br />
				也因為如此，斷線的檢查推波通知 <code>並非準確的5分鐘</code>
				'
			],
		];

		$records = [
			[
				'key' => '套件名稱',
				'value' => 'MainWP Monitoring Notify Extension'
			],
			[
				'key' => '版本號',
				'value' =>  MainWP_Monitoring_Notify_Extension::get_instance()->ver
			],
			[
				'key' => '開發者',
				'value' =>  '<a href="https://github.com/j7-dev" target="_black">J7 <i class="github icon"></i></a> '
			],
			[
				'key' => '程式碼倉庫',
				'value' =>  '<a href="https://github.com/j7-dev/MainWP-Monitoring-Notify" target="_black"><div class="ui labeled button" tabindex="0">
				<div class="ui basic blue button">
				<i class="github icon"></i> GitHub
				</div>
				<span class="ui basic left pointing blue label">
				⭐
				</span>
			</div></a><span style="margin-left:2rem;">您的星星是給開發者的肯定</span>'
			],
			[
				'key' => '開源贊助',
				'value' =>  '<a href="#" target="_black"><button class="ui blue button"><i class="coffee icon" style="color:#fff !important;"></i> 請我喝杯咖啡</button>
			</a>'
			],
			[
				'key' => 'Bug 回報',
				'value' =>  '<a href="https://github.com/j7-dev/MainWP-Monitoring-Notify/issues" target="_black"><button class="ui green button"><i class="bug icon" style="color:#fff !important;"></i> GitHub Issues</button>
			</a>'
			],
		]
	?>
		<div class="ui grid field">
			<div class="eight wide column">
				<?php self::renderQA($questions); ?>
			</div>
			<div class="eight wide column">
				<?php self::renderTable($records); ?>
			</div>
		</div>
	<?php

	}

	public static function render_form()
	{
	?>
		<div class="ui segment">
			<form method="post" enctype="multipart/form-data" id="mainwp-monitoring-notify-settings-page-form" class="ui form">
				<?php self::render_fields(); ?>
				<div class="mainwp-form-footer">
					<div class="ui divider"></div>
					<button id="monitoring_notify_submit_btn" class="ui big green button"><?php _e('Save Settings', 'mainwp-monitoring-notify-extension'); ?></button>
				</div>
				<div id="response_msg" style="margin-top:2rem"></div>

			</form>
		</div>
	<?php
	}

	public static function render_fields()
	{
		$prefix = MainWP_Monitoring_Notify_Extension::$prefix;
		$line_token = MainWP_Monitoring_Notify_Extension::get_instance()->line_token;
		$only_notify_when_site_offline = MainWP_Monitoring_Notify_Extension::get_instance()->only_notify_when_site_offline;
		$base_url = MainWP_Monitoring_Notify_Extension::get_instance()->plugin_url;
		$helpers = [
			[
				'content' => '請先前往 <a href="https://notify-bot.line.me/zh_TW/" target="_blank">LINE Notify</a> 並登入你的 LINE',
				'image' => ''
			],
			[
				'content' => '點擊 <code>Generate token</code> 然後選擇想要通知的聊天室',
				'image' => $base_url . '/assets/image/choose_chatroom.png'
			],
			[
				'content' => '複製 <code>token</code> 然後貼到這邊保存',
				'image' => $base_url . '/assets/image/get_token.png'
			],
			[
				'content' => '最後，邀請 LINE Notify 到你<code>第二步選擇的聊天室</code>就完成了🎉🎉🎉',
				'image' => $base_url . '/assets/image/invite.png'
			],
		];
		$modal_props = [
			'key' => 'tutorial',
			'label' => '<i class="info circle icon"></i> 教學',
			'title' => '如何申請 LINE Notify Token',
			'content' => ['MainWP_Monitoring_Notify_Settings', 'renderList'],
			'content_props' => $helpers
		];

	?>
		<div class="ui grid field">
			<label class="six wide column middle aligned"><?php _e('Line Notify Token', 'mainwp-monitoring-notify-extension'); ?><span style="margin-right:1rem;"></span><?php self::renderModal($modal_props) ?></label>
			<div class="ten wide column">
				<input type="text" name="<?= "{$prefix}line_token" ?>" id="<?= "{$prefix}line_token" ?>" value="<?= $line_token ?>" />
			</div>
		</div>
		<div class="ui grid field">
			<label class="six wide column middle aligned" data-tooltip="<?php esc_attr_e('Check this option to notify when site is offline only', 'mainwp-monitoring-notify-extension'); ?>" data-inverted="" data-position="top left"><?php _e('Only Notify When Site Offline', 'mainwp-monitoring-notify-extension'); ?></label>
			<div class="ten wide column">
				<input type="checkbox" name="<?= "{$prefix}only_notify_when_site_offline" ?>" id="<?= "{$prefix}only_notify_when_site_offline" ?>" style="position: relative;top: 7px;" <?php checked($only_notify_when_site_offline); ?> /> <span>check this if you don't want to receive every notification</span>
			</div>
		</div>

	<?php

	}

	public static function renderList($helpers)
	{
	?>
		<ol>
			<?php foreach ($helpers as $key => $helper) : ?>
				<li style="color: #666;">
					<p><?= $helper['content'] ?></p>
					<?php if (!empty($helper['image'])) : ?>
						<p><a href="<?= $helper['image'] ?>" target="_blank"><img style="width:10rem;" src="<?= $helper['image'] ?>" /></a></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	public static function renderQA($questions)
	{
		foreach ($questions as $key => $question) : ?>
			<div class="ui icon info message">

				<div class="content">
					<div class="header" style="margin-bottom:2rem;">
						<?= $question['q'] ?>
					</div>
					<p><?= $question['a'] ?></p>
				</div>
			</div>
		<?php endforeach;
	}

	public static function renderModal($modal_props)
	{
		$default_modal_props = [
			'key' => wp_unique_id(),
			'label' => '開啟 Modal',
			'title' => 'Modal Title',
			'content' => [],
			'content_props' => []
		];
		$modal_props = array_merge($default_modal_props, $modal_props);
		?>
		<button data-modal="<?= $modal_props['key'] ?>" class="mini ui blue button">
			<?= $modal_props['label'] ?>
		</button>
		<div id="<?= $modal_props['key'] ?>" class="ui modal">
			<i class="close icon"></i>
			<div class="header">
				<?= $modal_props['title'] ?>
			</div>
			<div class="content">
				<?php call_user_func_array($modal_props['content'], [$modal_props['content_props']]) ?>
			</div>
		</div>

		<script>
			(function($) {
				const btn = $('button[data-modal="<?= $modal_props['key'] ?>"]');
				btn.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					$('#<?= $modal_props['key'] ?>').modal('show');
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

				<?php foreach ($records as $record) : ?>
					<tr>
						<td data-label="key"><?= $record['key'] ?></td>
						<td data-label="value"><?= $record['value'] ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

<?php
	}

	public static function on_load_page()
	{
	}
}
