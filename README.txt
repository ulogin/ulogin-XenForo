=== uLogin - виджет авторизации через социальные сети ===
Donate link: http://ulogin.ru/
Tags: ulogin, login, social, authorization
Requires at least: 1.0.1
Tested up to: 1.0.4
Stable tag: 1.7
License: GPL V3

Форма авторизации uLogin через социальные сети. Улучшенный аналог loginza.

== Description ==

uLogin — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

== Installation ==

1. Скопировать все файлы и папки находящиеся в папке /upload в архиве в папку с xenForo (xenForo/library/uLogin - конечная папка).

2. Через административную панель установить дополнение. Для этого необходимо в открытом пункте меню "Install Add-on"  указать путь до файла addon-ulogin.xml(содержится в корне архива с дополнением).

3. Для отображения виджета необходимо исправить шаблон login_bar следующим образом:
	Найти:
		<h3 id="loginBarHandle">
			<label for="LoginControl"><a href="{xen:link login}" class="concealed noOutline">{xen:if $xenOptions.registrationSetup.enabled, {xen:phrase log_in_or_sign_up}, {xen:phrase log_in}}</a></label>
		</h3>
	Ниже добавить:
		<xen:hook name="ulogin"></xen:hook>

Шаблон можно редактировать через административную панель.

4. По необходимости изменить настройки продукта.
