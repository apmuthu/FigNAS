<?php
/*
	uk.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Authors: quix@free.fr, ck@realtime-projects.com.
	The Initial Developer of the Original Code is The QuiX project.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies
	of XigmaNAS, either expressed or implied.
*/
// Ukrainian Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

      // error
      "error"                => "Помилка",
      "back"                 => "Назад",

      // root
      "home"                 => "Домашній каталог користувача не існує, перевірте ваши налаштування...",
      "abovehome"            => "Поточний каталог не може знаходитися вище ніж домашній каталог користувача.",
      "targetabovehome"      => "Цільовий каталог не може знаходитися вище ніж домашній каталог користувача.",

      // exist
      "direxist"             => "Цей каталог не існує",
      //"filedoesexist"      => "Цей файл вже існує",
      "fileexist"            => "Цей файл не існує",
      "itemdoesexist"        => "Цей об'єкт вже існує",
      "itemexist"            => "Цей об'єкт не існує",
      "targetexist"          => "Цільовий каталог не існує",
      "targetdoesexist"      => "Цільовий об'єкт вже існує",

      // open
      "opendir"              => "Неможливо відкрити каталог",
      "readdir"              => "Неможливо прочитати вміст каталога",

      // access
      "accessdir"            => "Вам не дозволений доступ до цього каталога",
      "accessfile"           => "Вам не дозволений доступ до цього файла",
      "accessitem"           => "Вам не дозволений доступ до цього об'єкта",
      "accessfunc"           => "Вам не дозволено використовувати цю функцію",
      "accesstarget"         => "Вам не дозволений доступ до цільового каталога",

      // actions
      "chmod_not_allowed"    => "Змінення дозволів на \"Не встановлено\" не дозволено!",
      "permread"             => "Помилка отримання прав доступу",
      "permchange"           => "Помилка змінення прав доступу",
      "openfile"             => "Помилка відкривання файла",
      "savefile"             => "Помилка збереження файла",
      "createfile"           => "Помилка створення файла",
      "createdir"            => "Помилка створення каталога",
      "uploadfile"           => "Помилка завантаження файла",
      "copyitem"             => "Помилка копіювання",
      "moveitem"             => "Помилка переміщення",
      "delitem"              => "Помилка видалення",
      "chpass"               => "Помилка змінення паролю",
      "deluser"              => "Помилка видалення користувача",
      "adduser"              => "Помилка додавання користувача",
      "saveuser"             => "Помилка збереження користувача",
      "searchnothing"        => "Строка пошуку не повиння бути пустою",

      // misc
      "miscnofunc"           => "Функція не доступна",
      "miscfilesize"         => "Размір файла перевишує максимальний",
      "miscfilepart"         => "Файл був завантажений частково",
      "miscnoname"           => "Ви повинні вказати ім'я",
      "miscselitems"         => "Ви не обрали об'єкти",
      "miscdelitems"         => "Ви впевнені, що хочите видалити ці об'єкти (\"+num+\" шт.) ?",
      "miscdeluser"          => "Ви впевнені, що хочите видалити користувача '\"+user+\"' ?",
      "miscnopassdiff"       => "Новий пароль співпадає зі поточним",
      "miscnopassmatch"      => "Паролі не співпадають",
      "miscfieldmissed"      => "Всі важливі поля повинні бути заповнені",
      "miscnouserpass"       => "Неправильне ім'я користувача аба пароль",
      "miscselfremove"       => "Ви не можете видалити свій користувацький запис",
      "miscuserexist"        => "Такий користувач вже існує",
      "miscnofinduser"       => "Неможливо знайти користувача",
);
$GLOBALS["messages"] = array(

      // links
      "permlink"             => "Змінити права доступу",
      "editlink"             => "Редагувати",
      "downlink"             => "Завантажити",
      "download_selected"    => "Завантажити обрані файли",
      "uplink"               => "Догори",
      "homelink"             => "Кореневий каталог",
      "reloadlink"           => "Відновити",
      "copylink"             => "Копіювати",
      "movelink"             => "Перемістити",
      "dellink"              => "Видалити",
      "comprlink"            => "Архівувати",
      "adminlink"            => "Адміністрування",
      "logoutlink"           => "Вихід",
      "uploadlink"           => "Вивантажити",
      "searchlink"           => "Пошук",
      "unziplink"            => "Розархівувати",

      // list
      "nameheader"           => "Файл",
      "sizeheader"           => "Розмір",
      "typeheader"           => "Тип",
      "modifheader"          => "Змінений",
      "permheader"           => "Права доступу",
      "actionheader"         => "Дії",
      "pathheader"           => "Шлях",

      // buttons
      "btncancel"            => "Відмінити",
      "btnsave"              => "Зберегти",
      "btnchange"            => "Змінити",
      "btnreset"             => "Очистити",
      "btnclose"             => "Закрити",
      "btncreate"            => "Створити",
      "btnsearch"            => "Пошук",
      "btnupload"            => "Вивантажити",
      "btncopy"              => "Копіювати",
      "btnmove"              => "Перемістити",
      "btnlogin"             => "Вхід",
      "btnlogout"            => "Вихід",
      "btnadd"               => "Додати",
      "btnedit"              => "Редагувати",
      "btnremove"            => "Видалити",
      "btnunzip"             => "розархівувати",

      // actions
      "actdir"               => "Каталог",
      "actperms"             => "Змінити права доступу",
      "actedit"              => "Редагувати файл",
      "actsearchresults"     => "Результати пошуку",
      "actcopyitems"         => "Копіювати об'єкти",
      "actcopyfrom"          => "Копіювати з /%s до /%s ",
      "actmoveitems"         => "Перемістити об'єкти",
      "actmovefrom"          => "Перемістити з /%s до /%s ",
      "actlogin"             => "Увійти",
      "actloginheader"       => "Ласкаво просимо до QuiXplorer!",
      "actadmin"             => "Адміністрування",
      "actchpwd"             => "Змінення паролю",
      "actusers"             => "Користувачі",
      "actarchive"           => "Архівувати об'єкти",
      "actunzipitem"         => "Виймання",
      "actupload"            => "Вивантажити файли",

      // misc
      "miscitems"            => "Об'єкти",
      "miscfree"             => "Вільно",
      "miscusername"         => "Ім'я користувача",
      "miscpassword"         => "Пароль",
      "miscoldpass"          => "Старий пароль",
      "miscnewpass"          => "Новий пароль",
      "miscconfpass"         => "Підтвердження паролю",
      "miscconfnewpass"      => "Підтвердження нового паролю",
      "miscchpass"           => "Змінит пароль",
      "mischomedir"          => "Домашній каталог",
      "mischomeurl"          => "Домашній URL",
      "miscshowhidden"       => "Показувати приховані об'єкти",
      "mischidepattern"      => "Приховані об'єкти",
      "miscperms"            => "Права доступу",
      "miscuseritems"        => "ім'я, домашній каталог, показувати приховані об'єкти, права доступу, активність)",
      "miscadduser"          => "додавання користувача",
      "miscedituser"         => "редагування свойств користувача '%s'",
      "miscactive"           => "Активний",
      "misclang"             => "Мова",
      "miscnoresult"         => "Нічого не знайдено",
      "miscsubdirs"          => "Шукати в підкаталогах",
      "miscpermissions"      => array(
            "read"           => array("Read", "User may read and download a file"),
            "create"         => array("Write", "User may create a new file"),
            "change"         => array("Change", "User may change (upload, modify) an existing file"),
            "delete"         => array("Delete", "User may delete an existing file"),
            "password"       => array("Change password", "User may change the password"),
            "admin"          => array("Administrator", "Full access"),
            ),
      "miscyesno"            => array("Так","Ні","Т","Н"),
      "miscchmod"            => array("Власник", "Група", "Загальний"),
);

?>
