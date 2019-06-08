<?php
/*
	wui2.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

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
require_once 'config.inc';
require_once 'array.inc';

class HTMLBaseControl2 {
	var $_ctrlname = '';
	var $_title = '';
	var $_description = '';
	var $_value;
	var $_required = false;
	var $_readonly = false;
	var $_altpadding = false;
	var $_classtag = 'celltag';
	var $_classdata = 'celldata';
	var $_classaddonrequired = 'req';
	var $_classaddonpadalt = 'alt';
	//	constructor
	public function __construct($ctrlname,$title,$value,$description = '') {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetDescription($description);
		$this->SetValue($value);
	}
	//	get/set methods
	function SetAltPadding($bool) {
		$this->_altpadding = $bool;
	}
	function GetAltPadding() {
		return $this->_altpadding;
	}
	function SetClassAddonRequired($cssclass) {
		$this->_classaddonrequired = $cssclass;
	}
	function GetClassAddonRequired() {
		return $this->_classaddonrequired;
	}
	function SetClassAddonPadAlt($cssclass) {
		$this->_classaddonpadalt = $cssclass;
	}
	function GetClassAddonPadAlt() {
		return $this->_classaddonpadalt;
	}
	function SetClassData($cssclass) {
		$this->_classdata = $cssclass;
	}
	function GetClassData() {
		return $this->_classdata;
	}
	function SetClassTag($cssclass) {
		$this ->_classtag = $cssclass;
	}
	function GetClassTag() {
		return $this->_classtag;
	}
	function SetCtrlName($name) {
		$this->_ctrlname = $name;
	}
	function GetCtrlName() {
		return $this->_ctrlname;
	}
	function SetDescription($description) {
		$this->_description = $description;
	}
	function GetDescription() {
		return $this->_description;
	}
	function SetReadOnly($bool) {
		$this->_readonly = $bool;
	}
	function GetReadOnly() {
		return $this->_readonly;
	}
	function SetRequired($bool) {
		$this->_required = $bool;
	}
	function GetRequired() {
		return $this->_required;
	}
	function SetTitle($title) {
		$this->_title = $title;
	}
	function GetTitle() {
		return $this->_title;
	}
	function SetValue($value) {
		$this->_value = $value;
	}
	function GetValue() {
		return $this->_value;
	}
	//	support methods
	function GetClassOfTag() {
		$class = $this->GetClassTag();
		if(true === $this->GetRequired()):
			$class .= $this->GetClassAddonRequired();
		endif;
		if(true === $this->GetAltPadding()):
			$class .= $this->GetClassAddonPadAlt();
		endif;
		return $class;
	}
	function GetClassOfData() {
		$class = $this->GetClassData();
		if(true === $this->GetRequired()):
			$class .= $this->GetClassAddonRequired();
		endif;
		if(true === $this->GetAltPadding()):
			$class .= $this->GetClassAddonPadAlt();
		endif;
		return $class;
	}
	function GetDescriptionOutput() {
		//	description:
		//	string
		//	[string, ...]
		//	[ [string], ...]
		//	[ [string,no_br], ...]
		//	[ [string,color], ...]
		//	[ [string,color,no_br], ...]
		$description = $this->GetDescription();
		$description_output = '';
		$suppressbr = true;
		if(!empty($description)): // string or array
			if(is_string($description)):
				$description_output = $description;
			elseif(is_array($description)):
				foreach($description as $description_row):
					if(is_string($description_row)):
						if($suppressbr):
							$description_output .= $description_row;
							$suppressbr = false;
						else:
							$description_output .= ('<br />' . $description_row);
						endif;
					elseif(is_array($description_row)):
						switch(count($description_row)):
							case 1:
								if(is_string($description_row[0])):
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									$description_output .= $description_row[0];
								endif;
								break;
							case 2:
								if(is_string($description_row[0])):
									$color = NULL;
									if(is_string($description_row[1])):
										$color = $description_row[1];
									endif;
									if(is_bool($description_row[1])):
										$suppressbr = $description_row[1];
									endif;
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									if(is_null($color)):
										$description_output .= $description_row[0];
									else:
										$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
									endif;
								endif;
								break;
							case 3: // allow not to break
								if(is_string($description_row[0])):
									$color = NULL;
									if(is_string($description_row[1])):
										$color = $description_row[1];
									endif;
									if(is_bool($description_row[2])):
										$suppressbr = $description_row[2];
									endif;
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									if(is_null($color)):
										$description_output .= $description_row[0];
									else:
										$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
									endif;
								endif;
								break;
						endswitch;
					endif;
				endforeach;
			endif;
		endif;
		return $description_output;
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		$description = $this->GetDescriptionOutput();
		//	compose
		$attributes = ['id' => sprintf('%s_tr',$this->GetCtrlName())];
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTag()];
//		if($this->GetReadOnly()):
			$tr->insTD($attributes,$this->GetTitle());
//		else:
//			$tdtag = $tr->addTD($attributes);
//			$attributes = ['for' => $ctrlname];
//			$tdtag->addElement('label',$attributes,$this->GetTitle());
//		endif;
		$attributes = ['class' => $this->GetClassOfData()];
		$tddata = $tr->addTD($attributes);
		$this->ComposeInner($tddata);
		if(!empty($description)):
			$attributes = ['class' => 'formfldadditionalinfo'];
			$tddata->insDIV($attributes,$description);
		endif;
		return $anchor;
	}
	function ComposeInner(&$anchor) {
	}
}
class HTMLBaseControlJS2 extends HTMLBaseControl2 {
	var $_onclick = '';
	function SetJSonClick($code) {
		$this->_onclick = $code;
	}
	function GetJSonClick() {
		return $this->_onclick;
	}
}
class HTMLEditBox2 extends HTMLBaseControl2 {
	var $_size = 40;
	var $_maxlength = 0;
	var $_placeholder = '';
	var $_classinputtext = 'formfld';
	var $_classinputtextro = 'formfldro';

	//	constructor
	function __construct($ctrlname,$title,$value,$description,$size) {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetSize($size);
	}
	//	get/set methods
	function SetClassInputText($param) {
		$this->_classinputtext = $param;
	}
	function GetClassInputText() {
		return $this->_classinputtext;
	}
	function SetClassInputTextRO($param) {
		$this->_classinputtextro = $param;
	}
	function GetClassInputTextRO() {
		return $this->_classinputtextro;
	}
	function SetMaxLength($maxlength) {
		$this->_maxlength = $maxlength;
	}
	function GetMaxLength() {
		return $this->_maxlength;
	}
	function SetPlaceholder(string $placeholder = '') {
		$this->_placeholder = $placeholder;
	}
	function GetPlaceholder() {
		return $this->_placeholder;
	}
	function SetSize($size) {
		$this->_size = $size;
	}
	function GetSize() {
		return $this->_size;
	}
	//	support methods
	function GetAttributes(array &$attributes = []) {
		if(true === $this->GetReadOnly()):
			$attributes['readonly'] = 'readonly';
		endif;
		$tagval = $this->GetPlaceholder();
		if(preg_match('/\S/',$tagval)):
			$attributes['placeholder'] = $tagval;
		endif;
		$tagval = $this->GetMaxLength();
		if($tagval > 0):
			$attributes['maxlength'] = $tagval;
		endif;
		return $attributes;
	}
	function GetClassOfInputText() {
		if(true === $this->GetReadOnly()):
			return $this->GetClassInputTextRO();
		else:
			return $this->GetClassInputText();
		endif;
	}
	function ComposeInner(&$anchor) {
		$attributes = [
			'type' => 'text',
			'id' => $this->GetCtrlName(),
			'name' => $this->GetCtrlName(),
			'class' => $this->GetClassOfInputText(),
			'size' => $this->GetSize(),
			'value' => $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLPasswordBox2 extends HTMLEditBox2 {
	var $_classinputpassword = 'formfld';
	//	get/set methods
	function SetClassInputPassword($cssclass) {
		$this->_classinputpassword = $cssclass;
	}
	function GetClassInputPassword() {
		return $this->_classinputpassword;
	}
	//	support methods
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$attributes = [
			'type' => 'password',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfInputPassword(),
			'size' => $this->GetSize(),
			'value'=> $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLPasswordConfBox2 extends HTMLEditBox2 {
	var $_ctrlnameconf = '';
	var $_valueconf = '';
	var $_classinputpassword = 'formfld';
	var $_placeholderconfirm = '';
	//	constructor
	function __construct($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$description,$size) {
		parent::__construct($ctrlname,$title,$value,$description,$size);
		$this->SetCtrlNameConf($ctrlnameconf);
		$this->SetValueConf($valueconf);
	}
	//	get/set methods
	function SetClassInputPassword($cssclass) {
		$this->_classinputpassword = $cssclass;
	}
	function GetClassInputPassword() {
		return $this->_classinputpassword;
	}
	function SetCtrlNameConf($name) {
		$this->_ctrlnameconf = $name;
	}
	function GetCtrlNameConf() {
		return $this->_ctrlnameconf;
	}
	function SetPlaceholderConfirm(string $placeholder = '') {
		$this->_placeholderconfirm = $placeholder;
	}
	function GetPlaceholderConfirm() {
		return $this->_placeholderconfirm;
	}
	function SetValueConf($value) {
		$this->_valueconf = $value;
	}
	function GetValueConf() {
		return $this->_valueconf;
	}
	//	support methods
	function GetAttributesConfirm(array &$attributes = []) {
		$attributes = $this->GetAttributes($attributes);
		$tagval = $this->GetPlaceholderConfirm();
		if(preg_match('/\S/',$tagval)):
			$attributes['placeholder'] = $tagval;
		endif;
		return $attributes;
	}
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnameconf = $this->GetCtrlNameConf();
		$attributes = [
			'type' => 'password',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfInputPassword(),
			'size' => $this->GetSize(),
			'value'=> $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$o_div1 = $anchor->addDIV();
		$o_div1->insINPUT($attributes);
		$attributes = [
			'type' => 'password',
			'id' => $ctrlnameconf,
			'name' => $ctrlnameconf,
			'class' => $this->GetClassOfInputPassword(),
			'size' => $this->GetSize(),
			'value' => $this->GetValueConf()
		];
		$this->GetAttributesConfirm($attributes);
		$o_div2 = $anchor->addDIV();
		$o_div2->insINPUT($attributes);
	}
}
class HTMLTextArea2 extends HTMLEditBox2 {
	var $_columns = 40;
	var $_rows = 5;
	var $_wrap = true;
	var $_classtextarea = 'formpre';
	var $_classtextarearo = 'formprero';
	//	constructor
	function __construct($ctrlname,$title,$value,$description,$columns,$rows) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetDescription($description);
		$this->SetColumns($columns);
		$this->SetRows($rows);
	}
	//	get/set methods
	function SetClasstextarea($cssclass) {
		$this->_classtextarea = $cssclass;
	}
	function GetClassTextarea() {
		return $this->_classtextarea;
	}
	function SetClasstextareaRO($cssclass) {
		$this->_classtextarearo = $cssclass;
	}
	function GetClassTextareaRO() {
		return $this->_classtextarearo;
	}
	function SetColumns($columns) {
		$this->_columns = $columns;
	}
	function GetColumns() {
		return $this->_columns;
	}
	function SetRows($rows) {
		$this->_rows = $rows;
	}
	function GetRows() {
		return $this->_rows;
	}
	function SetWrap($bool) {
		$this->_wrap = $bool;
	}
	function GetWrap() {
		return $this->_wrap;
	}
	//	support methods
	function GetAttributes(array &$attributes = []) {
		parent::GetAttributes($attributes);
		if(false === $this->GetWrap()):
			$attributes['wrap'] = 'soft';
		else:
			$attributes['wrap'] = 'hard';
		endif;
		return $attributes;
	}
	function GetClassOfTextarea() {
		return ($this->GetReadOnly() ? $this->GetClassTextareaRO() : $this->GetClassTextarea());
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$attributes = [
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfTextarea(),
			'cols' => $this->GetColumns(),
			'rows' => $this->GetRows()
		];
		$this->GetAttributes($attributes);
		$anchor->addElement('textarea',$attributes,$this->GetValue());
	}
}
class HTMLFileChooser2 extends HTMLEditBox2 {
	var $_path = '';
	function __construct($ctrlname,$title,$value,$description,$size = 60) {
		parent::__construct($ctrlname,$title,$value,$description,$size);
	}
	function SetPath($path) {
		$this->_path = $path;
	}
	function GetPath() {
		return $this->_path;
	}
	function ComposeInner(&$anchor) {
		//	helper variables
		$ctrlname = $this->GetCtrlName();
		$size = $this->GetSize();
		//	input element
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $size
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
		//	file chooser
		$js = sprintf('%1$sifield = form.%1$s;',$ctrlname)
			. 'filechooser = window.open("filechooser.php?p="+'
			. sprintf('encodeURIComponent(%sifield.value)+',$ctrlname)
			. sprintf('"&sd=%s",',$this->GetPath())
			. '"filechooser",'
			. '"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");'
			. sprintf('filechooser.ifield = %sifield;',$ctrlname)
			. sprintf('window.ifield = %sifield;',$ctrlname);
		$attributes = [
			'type' => 'button',
			'id' => $ctrlname . 'browsebtn',
			'name' => $ctrlname . 'browsebtn',
			'class' => 'formbtn',
			'value' => $value,
			'size' => $size,
			'onclick' => $js,
			'value' => '...'
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLIPAddressBox2 extends HTMLEditBox2 {
	var $_ctrlnamenetmask = '';
	var $_valuenetmask = '';
	//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		$this->SetCtrlName($ctrlname);
		$this->SetCtrlNameNetmask($ctrlnamenetmask);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetValueNetmask($valuenetmask);
		$this->SetDescription($description);
	}
	//	get/set Methods
	function SetCtrlNameNetmask($name) {
		$this->_ctrlnamenetmask = $name;
	}
	function GetCtrlNameNetmask() {
		return $this->_ctrlnamenetmask;
	}
	function SetValueNetmask($value) {
		$this->_valuenetmask = $value;
	}
	function GetValueNetmask() {
		return $this->_valuenetmask;
	}
}
class HTMLIPv4AddressBox2 extends HTMLIPAddressBox2 {
	//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		parent::__construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description);
		$this->SetSize(20);
	}
	//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$valuenetmask = $this->GetValueNetmask();
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $this->GetSize()
		];
		$anchor->insINPUT($attributes);
		$slash = $anchor->ownerDocument->createTextNode(' / ');
		$anchor->appendChild($slash);
		$attributes = ['id' => $ctrlnamenetmask,'name' => $ctrlnamenetmask,'class' => 'formfld'];
		$o_select = $anchor->addElement('select',$attributes);
		foreach(range(1,32) as $netmask):
			$attributes = ['value' => $netmask];
			if($netmask == $valuenetmask):
				$attributes['selected'] = 'selected';
			endif;
			$o_select->addElement('option',$attributes,$netmask);
		endforeach;
	}
}
class HTMLIPv6AddressBox2 extends HTMLIPAddressBox2 {
	//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		parent::__construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description);
		$this->SetSize(60);
	}
	//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $this->GetSize()
		];
		$anchor->insINPUT($attributes);
		$slash = $anchor->ownerDocument->createTextNode(' / ');
		$anchor->appendChild($slash);
		$attributes = [
			'type' => 'text',
			'id' => $ctrlnamenetmask,
			'name' => $ctrlnamenetmask,
			'class' => 'formfld',
			'value' => $this->GetValueNetmask(),
			'size' => 2
		];
		$anchor->insINPUT($attributes);
	}
}
class HTMLCheckBox2 extends HTMLBaseControlJS2 {
	var $_caption = '';
	var $_classcheckbox = 'celldatacheckbox';
	var $_classcheckboxro = 'celldatacheckbox';
	//	constructor
	function __construct($ctrlname,$title,$value,$caption,$description = '') {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetCaption($caption);
	}
	//	get/set methods
	function SetChecked($bool) {
		$this->SetValue($bool);
	}
	function IsChecked() {
		return $this->GetValue();
	}
	function SetCaption($caption) {
		$this->_caption = $caption;
	}
	function GetCaption() {
		return $this->_caption;
	}
	function SetClassCheckbox($cssclass) {
		$this->_classcheckbox = $cssclass;
	}
	function GetClassCheckbox() {
		return $this->_classcheckbox;
	}
	function SetClassCheckboxRO($cssclass) {
		$this->_classcheckboxro = $cssclass;
	}
	function GetClassCheckboxRO() {
		return $this->_classcheckboxro;
	}
	//	support methods
	function GetAttributes(array &$attributes = []) {
		if(true === $this->IsChecked()):
			$attributes['checked'] = 'checked';
		endif;
		if(true === $this->GetReadOnly()):
			$attributes['disabled'] = 'disabled';
		endif;
		$onclick = $this->GetJSonClick();
		if(!empty($onclick)):
			$attributes['onclick'] = $onclick;
		endif;
		return $attributes;
	}
	function GetClassOfCheckbox() {
		return ($this->GetReadOnly() ? $this->GetClassCheckboxRO() : $this->GetClassCheckbox());
	}
	function ComposeInner(&$anchor) {
		//	helper variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$div = $anchor->addDIV(['class' => $this->GetClassOfCheckbox()]);
		$attributes = ['type' => 'checkbox','id' => $ctrlname,'name' => $ctrlname,'value' => 'yes'];
		$this->GetAttributes($attributes);
		$div->insINPUT($attributes);
		$div->addElement('label',['for' => $ctrlname],$this->GetCaption());
	}
}
class HTMLSelectControl2 extends HTMLBaseControlJS2 {
	var $_ctrlclass = '';
	var $_options = [];
	//	constructor
	function __construct($ctrlclass,$ctrlname,$title,$value,$options,$description) {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetCtrlClass($ctrlclass);
		$this->SetOptions($options);
	}
	//	get/set methods
	function SetCtrlClass($ctrlclass) {
		$this->_ctrlclass = $ctrlclass;
	}
	function GetCtrlClass() {
		return $this->_ctrlclass;
	}
	function SetOptions(array $options = []) {
		$this->_options = $options;
	}
	function GetOptions() {
		return $this->_options;
	}
	function GetAttributes(array &$attributes = []) {
		if(true === $this->GetReadOnly()):
			$attributes['disabled'] = 'disabled';
		endif;
		$onclick = $this->GetJSonClick();
		if(!empty($onclick)):
			$attributes['onclick'] = $onclick;
		endif;
		return $attributes;
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$attributes = [
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetCtrlClass()
		];
		$this->GetAttributes($attributes);
		$select = $anchor->addElement('select',$attributes);
		foreach($options as $option_tag => $option_val):
			$attributes = ['value' => $option_tag];
			if($value == $option_tag):
				$attributes['selected'] = 'selected';
			endif;
			$select->addElement('option',$attributes,$option_val);
		endforeach;
	}
}
class HTMLMultiSelectControl2 extends HTMLSelectControl2 {
	var $_size = 10;
	//	constructor
	function __construct($ctrlclass,$ctrlname,$title,$value,$options,$description) {
		parent::__construct($ctrlclass,$ctrlname,$title,$value,$options,$description);
	}
	//	get/set methods
	function GetSize() {
		return $this->_size;
	}
	function SetSize($size) {
		$this->_size = $size;
	}
	//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$attributes = [
			'id' => $ctrlname,
			'name' => sprintf('%s[]',$ctrlname),
			'class' => $this->GetCtrlClass(),
			'multiple' => 'multiple',
			'size' => $this->GetSize()
		];
		$this->GetAttributes($attributes);
		$select = $anchor->addElement('select',$attributes);
		foreach($options as $option_tag => $option_val):
			$attributes = ['value' => $option_tag];
			if(is_array($value) && in_array($option_tag,$value)):
				$attributes['selected'] = 'selected';
			endif;
			$select->addElement('option',$attributes,$option_val);
		endforeach;
	}
}
class HTMLComboBox2 extends HTMLSelectControl2 {
	//	constructor
	function __construct($ctrlname,$title,$value,$options,$description) {
		parent::__construct('formfld',$ctrlname,$title,$value,$options,$description);
	}
}
class HTMLRadioBox2 extends HTMLComboBox2 {
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$table = $anchor->addTABLE(['class' => 'area_data_selection']);
		$colgroup = $table->addCOLGROUP();
		$colgroup->insCOL(['style' => 'width:5%']);
		$colgroup->insCOL(['style' => 'width:95%']);
		$thead = $table->addTHEAD();
		$tr = $thead->addTR();
		$tr->insTHwC('lhelc');
		$tr->insTHwC('lhebl',$this->GetTitle());
		$tbody = $table->addTBODY();
		foreach($options as $option_tag => $option_val):
			//	create a unique identifier for each row.
			//	use label tag for text column to allow enabling the radio button by clicking on the text
			$uuid = sprintf('radio_%s',uuid());
			$tr = $tbody->addTR();
			$tdl = $tr->addTDwC('lcelc');
			$attributes = [
				'name' => $ctrlname,
				'value' => $option_tag,
				'type' => 'radio',
				'id' => $uuid
			];
			if($value === (string)$option_tag):
				$attributes['checked'] = 'checked';
			endif;
			$tdl->insINPUT($attributes);
			$tdr = $tr->addTDwC('lcebl');
			$tdr->addElement('label',['for' => $uuid,'style' => 'white-space:pre-wrap;'],$option_val);
		endforeach;
	}
}
class HTMLMountComboBox2 extends HTMLComboBox2 {
	//	constructor
	function __construct($ctrlname,$title,$value,$description) {
		global $config;
		//	generate options.
		$a_mounts = &array_make_branch($config,'mounts','mount');
		array_sort_key($a_mounts,'devicespecialfile');
		$options = [];
		$options[''] = gettext('Must choose one');
		foreach($a_mounts as $r_mount):
			$options[$r_mount['uuid']] = $r_mount['sharename'];
		endforeach;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLTimeZoneComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		//	get time zone data.
		function is_timezone($elt) {
			return !preg_match("/\/$/",$elt);
		}
		exec('/usr/bin/tar -tf /usr/share/zoneinfo.txz',$timezonelist);
		$timezonelist = array_filter($timezonelist,'is_timezone');
		sort($timezonelist);
		//	generate options.
		$options = [];
		foreach($timezonelist as $tzv):
			if(!empty($tzv)):
				$tzv = substr($tzv,2); // Remove leading './'
				$options[$tzv] = $tzv;
			endif;
		endforeach;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLLanguageComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		global $g_languages;
		//	generate options.
		$options = [];
		foreach($g_languages as $key => $val):
			if('auto' == $key):
				$options[$key] = gettext('Autodetect');
			else:
				$options[$key] = locale_get_display_name($key,$key);
			endif;
		endforeach;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLInterfaceComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		global $config;
		//	generate options.
		$options = ['lan' => 'LAN'];
		for($i = 1;isset($config['interfaces']['opt' . $i]);$i++):
			if(isset($config['interfaces']['opt' . $i]['enable'])):
				$options['opt' . $i] = $config['interfaces']['opt' . $i]['descr'];
			endif;
		endfor;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLListBox2 extends HTMLMultiSelectControl2 {
	function __construct($ctrlname,$title,$value,$options,$description) {
		parent::__construct('formselect',$ctrlname,$title,$value,$options,$description);
	}
}
class HTMLCheckboxBox2 extends HTMLListBox2 {
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$table = $anchor->addTABLE(['class' => 'area_data_selection']);
		$colgroup = $table->addCOLGROUP();
		$colgroup->insCOL(['style' => 'width:5%']);
		$colgroup->insCOL(['style' => 'width:95%']);
		$thead = $table->addTHEAD();
		$tr = $thead->addTR();
		$tr->insTHwC('lhelc');
		$tr->insTHwC('lhebl',$this->GetTitle());
		$tbody = $table->addTBODY();
		foreach($options as $option_tag => $option_val):
			//	create a unique identifier for each row.
			//	use label tag for text column to allow toggling the checkbox button by clicking on the text
			$uuid = sprintf('checkbox_%s',uuid());
			$tr = $tbody->addTR();
			$tdl = $tr->addTDwC('lcelc');
			$attributes = [
				'name' => sprintf('%s[]',$ctrlname),
				'value' => $option_tag,
				'type' => 'checkbox',
				'id' => $uuid
			];
			if(is_array($value) && in_array($option_tag,$value)):
				$attributes['checked'] = 'checked';
			endif;
			$tdl->insINPUT($attributes);
			$tdr = $tr->addTDwC('lcebl');
			$tdr->addElement('label',['for' => $uuid,'style' => 'white-space:pre-wrap;'],$option_val);
		endforeach;
	}
}
class HTMLSeparator2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_classseparator = 'gap';
	// constructor
	function __construct() {
	}
	//	get/set methods
	function SetClassSeparator($cssclass) {
		$this->_classseparator = $cssclass;
	}
	function GetClassSeparator() {
		return $this->_classseparator;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
	//	support methods
	function GetClassOfSeparator() {
		return $this->GetClassSeparator();
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$attributes = [];
		if(preg_match('/\S/',$ctrlname)):
			$attributes['id'] = $ctrlname;
		endif;
		$o_tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfSeparator(),'colspan' => $this->GetColSpan()];
		$o_tr->addTD($attributes);
		return $anchor;
	}
}
class HTMLTitleLine2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_classtopic = 'lhetop';
	//	get/set methods
	function SetClassTopic($cssclass) {
		$this->_classtopic = $cssclass;
	}
	function GetClassTopic() {
		return $this->_classtopic;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
	//	support methods
	function GetClassOfTopic() {
		return $this->GetClassTopic();
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$attributes = [];
		if(preg_match('/\S/',$ctrlname)):
			$attributes['id'] = $ctrlname;
		endif;
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTopic(),'colspan' => $this->GetColSpan()];
		$th = $tr->addTH($attributes,$this->GetTitle());
		return $anchor;
	}
}
class HTMLTitleLineCheckBox2 extends HTMLCheckBox2 {
	var $_colspan = 2;
	var $_classtopic = 'lhetop';
	//	constructor
	function __construct($ctrlname,$title,$value,$caption) {
		parent::__construct($ctrlname,$title,$value,$caption);
	}
	//	get/set methods
	function SetClassTopic($cssclass) {
		$this->_classtopic = $cssclass;
	}
	function GetClassTopic() {
		return $this->_classtopic;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
	//	support methods
	function GetClassOfTopic() {
		return $this->GetClassTopic();
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$attributes = ['id' => sprintf('%s_tr',$ctrlname)];
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTopic(),'colspan' => $this->GetColSpan()];
		$th = $tr->addTH($attributes);
		$attributes = ['style' => 'float:left'];
		$spanleft = $th->addSPAN($attributes,$this->GetTitle());
		$attributes = ['style' => 'float:right'];
		$spanright = $th->addSPAN($attributes);
		$label = $spanright->addElement('label');
		$attributes = ['type' => 'checkbox','id' => $ctrlname,'name' => $ctrlname,'class' => 'formfld cblot','value' => 'yes'];
		$this->getAttributes($attributes);
		$label->insINPUT($attributes);
		$attributes = ['class' => 'cblot'];
		$label->addSPAN($attributes,$this->GetCaption());
		return $anchor;
	}
}
class HTMLText2 extends HTMLBaseControl2 {
	//	constructor
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetReadOnly(true);
		$this->SetRequired(false);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	//	support methods
	function ComposeInner(&$anchor) {
		//	compose
		$anchor->addSPAN([],$this->GetValue());
	}
}
class HTMLTextInfo2 extends HTMLBaseControl2 {
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$attributes = ['id' => sprintf('%s_tr',$ctrlname)];
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTag()];
		$tdtag = $tr->addTD($attributes,$this->GetTitle());
		$attributes = ['class' => $this->GetClassOfData()];
		$tddata = $tr->addTD($attributes);
		$attributes = ['id' => $ctrlname];
		$tddata->addSPAN($attributes,$this->getValue());
		return $anchor;
	}
}
class HTMLRemark2 extends HTMLBaseControl2 {
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	function Compose(DOMNode &$anchor = NULL) {
		//	create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new co_DOMDocument();
		endif;
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		//	compose
		$attributes = ['id' => $ctrlname];
		$div1 = $anchor->addDIV($attributes);
		$attributes = ['class' => 'red'];
		$div1->addElement('strong',$attributes,$this->GetTitle());
		$attributes = [];
		$div2 = $anchor->addDIV($attributes,$this->GetValue());
		return $anchor;
	}
}
class HTMLFolderBox2 extends HTMLBaseControl2 {
	var $_path = '';

	function __construct($ctrlname,$title,$value,$description = '') {
		parent::__construct($ctrlname,$title,$value,$description);
	}
	function GetPath() {
		return $this->_path;
	}
	function SetPath($path) {
		$this->_path = $path;
	}
	function ComposeInner(&$anchor) {
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		$ctrlnamedata = $ctrlname . 'data';
		$value = $this->GetValue();
		//	control code for folders
		$t = [];
		$t[] = sprintf('function onchange_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('document.getElementById("%s").value = document.getElementById("%s").value;',$ctrlnamedata,$ctrlname);
		$t[] = '}';
		$t[] = sprintf('function onclick_add_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var value = document.getElementById("%s").value;',$ctrlnamedata);
		$t[] = "\t" . 'if (value != "") {';
		$t[] = "\t\t" . 'var found = false;';
		$t[] = "\t\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t\t" . 'if (element.options[i].text == value) {';
		$t[] = "\t\t\t\t" . 'found = true;';
		$t[] = "\t\t\t\t" . 'break;';
		$t[] = "\t\t\t" . '}';
		$t[] = "\t\t" . '}';
		$t[] = "\t\t" . 'if (found != true) {';
		$t[] = "\t\t\t" . 'element.options[element.length] = new Option(value, value, false, true);';
		$t[] = "\t\t\t" . sprintf('document.getElementById("%s").value = "";',$ctrlnamedata);
		$t[] = "\t\t" . '}';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onclick_delete_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . sprintf('var msg = confirm("%s");',unicode_escape_javascript(gettext('Do you really want to remove the selected item from the list?')));
		$t[] = "\t\t" . 'if (msg == true) {';
		$t[] = "\t\t\t" . 'element.options[element.selectedIndex] = null;';
		$t[] = "\t\t\t" . sprintf('document.getElementById("%s").value = "";',$ctrlnamedata);
		$t[] = "\t\t" . '}';
		$t[] = "\t" . '} else {';
		$t[] = "\t\t" . sprintf('alert("%s");',unicode_escape_javascript(gettext('Select item to remove from the list')));
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onclick_change_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . sprintf('var value = document.getElementById("%s").value;',$ctrlnamedata);
		$t[] = "\t\t" . 'element.options[element.selectedIndex].text = value;';
		$t[] = "\t\t" . 'element.options[element.selectedIndex].value = value;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onsubmit_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t" . 'if (element.options[i].value != "")';
		$t[] = "\t\t\t" . 'element.options[i].selected = true;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$anchor->addJavaScript(implode(PHP_EOL,$t));
		//	section 1: select + delete
		$div1 = $anchor->addDIV();
		//	selected folder
		$attributes = [
			'id' => $ctrlname,
			'name' => sprintf('%s[]',$ctrlname),
			'class' => 'formfld',
			'multiple' => 'multiple',
			'size' => '4',
			'style' => 'width:350px',
			'onchange' => sprintf('onchange_%s()',$ctrlname)
		];
		$select = $div1->addElement('select',$attributes);
		foreach ($value as $value_key => $value_val):
			$attributes = ['value' => $value_val];
			$select->addElement('option',$attributes,$value_val);
		endforeach;
		//	delete button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sdeletebtn',$ctrlname),
			'name' => sprintf('%sdeletebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Delete'),
			'onclick' => sprintf('onclick_delete_%s()',$ctrlname)
		];
		$div1->insINPUT($attributes);
		//	section 2: choose, add + change
		$div2 = $anchor->addDIV();
		//	path input field
		$attributes = [
			'type' => 'text',
			'id' => sprintf('%sdata',$ctrlname),
			'name' => sprintf('%sdata',$ctrlname),
			'class' => 'formfld',
			'value' => '',
			'size' => 60
		];
		$div2->insINPUT($attributes);
		//	choose button
		$js = sprintf('ifield = form.%s;',$ctrlnamedata)
			. ' filechooser = window.open("filechooser.php'
			. '?p="+encodeURIComponent(ifield.value)+"'
			. sprintf('&sd=%s",',$this->GetPath())
			. ' "filechooser",'
			. ' "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");'
			. ' filechooser.ifield = ifield;'
			. ' window.ifield = ifield;';
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sbrowsebtn',$ctrlname),
			'name' => sprintf('%sbrowsebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => '...',
			'onclick' => $js
		];
		$div2->insINPUT($attributes);
		//	add button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%saddbtn',$ctrlname),
			'name' => sprintf('%saddbtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Add'),
			'onclick' => sprintf('onclick_add_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
		//	change button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%schangebtn',$ctrlname),
			'name' => sprintf('%schangebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Change'),
			'onclick' => sprintf('onclick_change_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
	}
}
class HTMLFolderBox12 extends HTMLFolderBox2 {
	function ComposeInner(&$anchor) {
		//	helping variables
		$ctrlname = $this->GetCtrlName();
		$ctrlnamedata = $ctrlname . 'data';
		$ctrlnamefiletype = $ctrlname . 'filetype';
		$value = $this->GetValue();
		//	control code for folders
		$t = [];
		$t[] = 'function onchange_' . $ctrlname . '() {';
		$t[] = "\t" . 'var value1 = document.getElementById("' . $ctrlname . '");';
		$t[] = "\t" . 'if (value1.value.charAt(0) != "/") {';
		$t[] = "\t" . 'document.getElementById("' . $ctrlnamedata . '").value = value1.value.substring(2,(value1.value.length));';
		$t[] = "\t" . 'document.getElementById("' . $ctrlnamefiletype . '").value = value1.value.charAt(0);';
		$t[] = "\t\t" . '}else{';
		$t[] = "\t" . 'document.getElementById("' . $ctrlnamedata . '").value = document.getElementById("' . $ctrlname . '").value;';
		$t[] = "\t" . 'document.getElementById("' . $ctrlnamefiletype . '").value = "";';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = 'function onclick_add_' . $ctrlname . '() {';
		$t[] = "\t" . 'var value1 = document.getElementById("' . $ctrlnamedata . '").value;';
		$t[] = "\t" . 'var valuetype = document.getElementById("' . $ctrlnamefiletype . '").value;';
		$t[] = "\t" . 'if (valuetype != "") {';
		$t[] = "\t\t" . 'var valuetype = valuetype + ",";';
		$t[] = "\t" . '}';
		$t[] = "\t" . 'var value = valuetype +  value1;';
		$t[] = "\t" . 'if (value != "") {';
		$t[] = "\t\t" . 'var found = false;';
		$t[] = "\t\t" . 'var element = document.getElementById("' . $ctrlname . '");';
		$t[] = "\t\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t\t" . 'if (element.options[i].text == value) {';
		$t[] = "\t\t\t\t" . 'found = true;';
		$t[] = "\t\t\t\t" . 'break;';
		$t[] = "\t\t\t" . '}';
		$t[] = "\t\t" . '}';
		$t[] = "\t\t" . 'if (found != true) {';
		$t[] = "\t\t\t" . 'element.options[element.length] = new Option(value, value, false, true);';
		$t[] = "\t\t\t" . 'document.getElementById("' . $ctrlnamedata . '").value = "";';
		$t[] = "\t\t" . '}';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = 'function onclick_delete_' . $ctrlname . '() {';
		$t[] = "\t" . 'var element = document.getElementById("' . $ctrlname . '");';
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . 'var msg = confirm("' . unicode_escape_javascript(gettext('Do you really want to remove the selected item from the list?')) . '");';
		$t[] = "\t\t" . 'if (msg == true) {';
		$t[] = "\t\t\t" . 'element.options[element.selectedIndex] = null;';
		$t[] = "\t\t\t" . 'document.getElementById("' . $ctrlnamedata . '").value = "";';
		$t[] = "\t\t" . '  }';
		$t[] = "\t" . '} else {';
		$t[] = "\t\t" . 'alert("' . unicode_escape_javascript(gettext('Select item to remove from the list')) . '");';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = 'function onclick_change_' . $ctrlname . '() {';
		$t[] = "\t" . 'var element = document.getElementById("' . $ctrlname . '");';
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . 'var value1 = document.getElementById("' . $ctrlnamedata . '").value;';
		$t[] = "\t" . 'var valuetype = document.getElementById("' . $ctrlnamefiletype . '").value;';
		$t[] = "\t" . 'if (valuetype != "") {';
		$t[] = "\t\t" . 'var valuetype = valuetype + ",";';
		$t[] = "\t" . '}';
		$t[] = "\t" . 'var value = valuetype +  value1;';
		$t[] = "\t\t" . 'element.options[element.selectedIndex].text = value;';
		$t[] = "\t\t" . 'element.options[element.selectedIndex].value = value;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = 'function onsubmit_' . $ctrlname . '() {';
		$t[] = "\t" . 'var element = document.getElementById("' . $ctrlname . '");';
		$t[] = "\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t" . 'if (element.options[i].value != "")';
		$t[] = "\t\t\t" . 'element.options[i].selected = true;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$anchor->addJavaScript(implode(PHP_EOL,$t));
		//	section 1: select + delete
		$div1 = $anchor->addDIV();
		//	selected folder
		$attributes = ['id' => $ctrlname,'name' => sprintf('%s[]',$ctrlname),'class' => 'formfld','multiple' => 'multiple','style' => 'width:350px','onchange' => sprintf('onchange_%s()',$ctrlname)];
		$select = $div1->addElement('select',$attributes);
		foreach ($value as $value_key => $value_val):
			$attributes = ['value' => $value_val];
			$select->addElement('option',$attributes,$value_val);
		endforeach;
		//	delete button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sdeletebtn',$ctrlname),
			'name' => sprintf('%sdeletebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Delete'),
			'onclick' => sprintf('onclick_delete_%s()',$ctrlname)
		];
		$div1->insINPUT($attributes);
		//	section 2: choose, add + change
		$div2 = $anchor->addDIV();
		//	media type
		$attributes = ['id' => sprintf('%sfiletype',$ctrlname),'name' => sprintf('%sfiletype',$ctrlname),'class' => 'formfld'];
		$select = $div2->addElement('select',$attributes);
		$attributes = ['value' => '','selected' => 'selected'];
		$select->addElement('option',$attributes,gettext('All'));
		$attributes = ['value' => 'A'];
		$select->addElement('option',$attributes,gettext('Audio'));
		$attributes = ['value' => 'V'];
		$select->addElement('option',$attributes,gettext('Video'));
		$attributes = ['value' => 'P'];
		$select->addElement('option',$attributes,gettext('Pictures'));
		//	path input field
		$attributes = [
			'type' => 'text',
			'id' => sprintf('%sdata',$ctrlname),
			'name' => sprintf('%sdata',$ctrlname),
			'class' => 'formfld',
			'value' => '',
			'size' => 60
		];
		$div2->insINPUT($attributes);
		//	choose button
		$js = sprintf('ifield = form.%s;',$ctrlnamedata)
			. ' filechooser = window.open("filechooser.php'
			. '?p="+encodeURIComponent(ifield.value)+"'
			. sprintf('&sd=%s",',$this->GetPath())
			. ' "filechooser",'
			. ' "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");'
			. ' filechooser.ifield = ifield;'
			. ' window.ifield = ifield;';
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sbrowsebtn',$ctrlname),
			'name' => sprintf('%sbrowsebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => '...',
			'onclick' => $js
		];
		$div2->insINPUT($attributes);
		//	add button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%saddbtn',$ctrlname),
			'name' => sprintf('%saddbtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Add'),
			'onclick' => sprintf('onclick_add_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
		//	change button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%schangebtn',$ctrlname),
			'name' => sprintf('%schangebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Change'),
			'onclick' => sprintf('onclick_change_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
	}
}
trait co_DOMTools {
/**
 *	Appends a child node to an element and returns the new node.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $subnode
 */
	public function addElement(string $name,array $attributes = [],string $value = NULL,string $namespaceURI = NULL) {
		$subnode = $this->appendChild(new co_DOMElement($name,NULL,$namespaceURI));
		$subnode->import_soup($value);
		$subnode->addAttributes($attributes);
		return $subnode;
	}
/**
 *	Appends a child node to an element and returns the element.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $this
 */
	public function insElement(string $name,array $attributes = [],string $value = NULL,string $namespaceURI = NULL) {
		$subnode = $this->appendChild(new co_DOMElement($name,NULL,$namespaceURI));
		$subnode->import_soup($value);
		$subnode->addAttributes($attributes);
		return $this;
	}
/**
 *	Inserts a child node on top of the children of an element and returns the new node.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $subnode
 */
	public function prepend_element(string $name,array $attributes = [],string $value = NULL,string $namespaceURI = NULL) {
		if(is_null($this->firstChild)):
			$subnode = $this->appendChild(new co_DOMElement($name,NULL,$namespaceURI));
		else:
			$subnode = $this->insertBefore(new co_DOMElement($name,NULL,$namespaceURI),$this->firstChild);
		endif;
		$subnode->import_soup($value);
		$subnode->addAttributes($attributes);
		return $subnode;
	}
/**
 *	Appends a child node to an element and returns the element.<br/>
 *	If the string contains html tags, loadHTML is called, otherwise a<br/>
 *	text node is created.
 *	@param string $value The text/html string
 *	@return $this
 */
	public function import_soup(string $value = NULL) {
		if(!is_null($value)):
//			rough check if value contains html code, if found try to import as HTML, otherwise add as text
			$html_import_successful = false;
			if(preg_match('~/[a-z]*>~i',$value)):
				$backup_use_internal_errors = libxml_use_internal_errors(true);
				$backup_disable_entity_loader = libxml_disable_entity_loader(true);
				$document = $this->ownerDocument ?? $this;
				$htmldocument = new DOMDocument('1.0','UTF-8');
				$html_import_successful = $htmldocument->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $value . '</body></html>',LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				libxml_clear_errors();
				libxml_disable_entity_loader($backup_disable_entity_loader);
				libxml_use_internal_errors($backup_use_internal_errors);
			endif;
			if($html_import_successful):
				$items = $htmldocument->getElementsByTagName('body');
				foreach($items as $item):
					foreach($item->childNodes as $childnode):
						$newnode = $document->importNode($childnode,true);
						$this->appendChild($newnode);
					endforeach;
				endforeach;
			else:
				$document = $this->ownerDocument ?? $this;
				$this->appendChild($document->createTextNode($value));
			endif;
		endif;
		return $this;
	}
/**
 *	Appends a JavaScript node to the DOM
 *	@param string $text
 *	@return DOMNode $this
 */
	public function addJavaScript(string $text = '') {
		if(preg_match('/\S/',$text)):
			$node = $this->addElement('script');
			if(false !== $node):
				$opening = $node->ownerDocument->createTextNode(PHP_EOL . '//<![CDATA[' . PHP_EOL);
				$ending = $node->ownerDocument->createTextNode(PHP_EOL . '//]]>' . PHP_EOL);
				if((false !== $opening) && (false !== $ending)):
					$node->appendChild($opening);
					$cdata = $node->ownerDocument->createCDATASection($text);
					if(false !== $cdata):
						$node->appendChild($cdata);
					endif;
					$node->appendChild($ending);
				endif;
			endif;
		endif;
		return $this;
	}
	//	tags
	public function addA(array $attributes = [],string $value = NULL) {
		return $this->addElement('a',$attributes,$value);
	}
	public function insA(array $attributes = [],string $value = NULL) {
		return $this->insElement('a',$attributes,$value);
	}
	public function insCOL(array $attributes = []) {
		return $this->insElement('col',$attributes);
	}
	public function addDIV(array $attributes = [],string $value = NULL) {
		return $this->addElement('div',$attributes,$value);
	}
	public function insDIV(array $attributes = [],string $value = NULL) {
		return $this->insElement('div',$attributes,$value);
	}
	public function addFORM(array $attributes = [],string $value = NULL) {
		return $this->addElement('form',$attributes,$value);
	}
	public function insIMG(array $attributes = []) {
		return $this->insElement('img',$attributes);
	}
	public function insINPUT(array $attributes = [],string $value = NULL) {
		return $this->insElement('input',$attributes,$value);
	}
	public function addLI(array $attributes = [],string $value = NULL) {
		return $this->addElement('li',$attributes,$value);
	}
	public function addP(array $attributes = [],string $value = NULL) {
		return $this->addElement('p',$attributes,$value);
	}
	public function addSPAN(array $attributes = [],string $value = NULL) {
		return $this->addElement('span',$attributes,$value);
	}
	public function insSPAN(array $attributes = [],string $value = NULL) {
		return $this->insElement('span',$attributes,$value);
	}
	public function addUL(array $attributes = [],string $value = NULL) {
		return $this->addElement('ul',$attributes,$value);
	}
	//	table tags
	public function addTABLE(array $attributes = [],string $value = NULL) {
		return $this->addElement('table',$attributes,$value);
	}
	public function addCOLGROUP(array $attributes = [],string $value = NULL) {
		return $this->addElement('colgroup',$attributes,$value);
	}
	public function addTHEAD(array $attributes = [],string $value = NULL) {
		return $this->addElement('thead',$attributes,$value);
	}
	public function addTBODY(array $attributes = [],string $value = NULL) {
		return $this->addElement('tbody',$attributes,$value);
	}
	public function addTFOOT(array $attributes = [],string $value = NULL) {
		return $this->addElement('tfoot',$attributes,$value);
	}
	public function addTR(array $attributes = [],string $value = NULL) {
		return $this->addElement('tr',$attributes,$value);
	}
	public function addTD(array $attributes = [],string $value = NULL) {
		return $this->addElement('td',$attributes,$value);
	}
	public function insTD(array $attributes = [],string $value = NULL) {
		return $this->insElement('td',$attributes,$value);
	}
	public function addTDwC(string $class,string $value = NULL) {
		return $this->addElement('td',['class' => $class],$value);
	}
	public function insTDwC(string $class,string $value = NULL) {
		return $this->insElement('td',['class' => $class],$value);
	}
	public function addTH(array $attributes = [],string $value = NULL) {
		return $this->addElement('th',$attributes,$value);
	}
	public function insTH(array $attributes = [],string $value = NULL) {
		return $this->insElement('th',$attributes,$value);
	}
	public function addTHwC(string $class,string $value = NULL) {
		return $this->addElement('th',['class' => $class],$value);
	}
	public function insTHwC(string $class,string $value = NULL) {
		return $this->insElement('th',['class' => $class],$value);
	}
	//	tab menu fragments and macros
/**
 *
 *	@return DOMNode $subnode
 */
	public function add_area_tabnav() {
		$table_attributes = ['id' => 'area_navigator'];
		$document = $this->ownerDocument ?? $this;
		$target = $document->getElementById('g4h');
		if(isset($target)):
			$append_mode = true; // last element of header section
			$div_attributes = [
				'id' => 'area_tabnav',
				'style' => 'padding: 0em 2em;'
			];
			if($append_mode):
				$subnode = $target->
					addDIV($div_attributes)->
						addTABLE($table_attributes)->
							addTBODY();
			else:
				$subnode = $target->
					prepend_element('div',$div_attributes)->
						addTABLE($table_attributes)->
							addTBODY();
			endif;
		else: // workaround for unconverted pages because of padding
			$target = $this;
			$subnode = $target->
				addTABLE($table_attributes)->
					addTBODY();
		endif;
		return $subnode;
	}
/**
 *	Creates tags for upper navigation menu
 *	@return DOMNode $subnode
 */
	public function add_tabnav_upper() {
		$subnode = $this->
			addTR()->
				addTDwC('tabnavtbl')->
					addUL(['id' => 'tabnav']);
		return $subnode;
	}
/**
 *	Creates tags for lower navigation menu
 *	@return DOMNode $subnode
 */
	public function add_tabnav_lower() {
		$subnode = $this->
			addTR()->
				addTDwC('tabnavtbl')->
					addUL(['id' => 'tabnav2']);
		return $subnode;
	}
/**
 *	Adds a menu item to the navigation menu
 *	@param string $href Link to script
 *	@param string $value Name of the menu item
 *	@param string $title Title of the menu item
 *	@param bool $active Flag to indicate an active menu item
 *	@return object $this
 */
	public function ins_tabnav_record(string $href = '',string $value = '',string $title = '',bool $active = false) {
		$attributes = [];
		if(preg_match('/\S/',$href)):
			$attributes['href'] = $href;
		endif;
		if(preg_match('/\S/',$title)):
			$attributes['title'] = $title;
		endif;
		$this->
			addLI(['class' => $active ? 'tabact' : 'tabinact'])->
				addA($attributes)->
					addSPAN([],$value);
		return $this;
	}
	/**
	 *
	 *	@return DOMNode $subnode
	 */
	public function add_area_data() {
		$subnode = $this->
			addTABLE(['id' => 'area_data'])->
				addTBODY()->
					addTR()->
						addTD(['id' => 'area_data_frame']);
		return $subnode;
	}
	public function ins_input_errors(array $input_errors = []) {
		global $g_img;

		$this->reset_hooks();
		if(is_array($input_errors)):
			$id = 'errorbox';
			$src = $g_img['box.error'];
			$alt = '';
			$firstrowtrigger = true;
			foreach($input_errors as $rowvalue):
				if(is_string($rowvalue) && preg_match('/\S/',$rowvalue)):
					if($firstrowtrigger):
						$hook_id = $this->addDIV(['id' => $id]);
						$mbcl1 = $hook_id->addDIV(['class' => 'mbcl-1']);
						$this->add_hook($mbcl1,'mbcl-1');
						$mbcl2 = $mbcl1->addDIV(['class' => 'mbcl-2 mbci-min']);
						$mbcl2i1 = $mbcl2->addDIV(['class' => 'icon mbci-min']);
						$mbcl2i2 = $mbcl2->addDIV(['class' => 'message mbci-max']);
						$mbcl3 = $mbcl2i2->addDIV(['class' => 'mbcl-3 mbci-min']);
						$mbcl2i1->insIMG(['src' => $src,'alt' => $alt]);
						$hook_messages = $mbcl3->
							addDIV([],sprintf('%s:',gettext('The following errors were detected'),':'))->
								addUL();
						$this->add_hook($hook_messages,'messages');
						$firstrowtrigger = false;
					endif;
					$hook_messages->addLI([],htmlspecialchars_decode($rowvalue,ENT_QUOTES|ENT_HTML5));
//					$hook_messages->addLI([],$rowvalue);
				endif;
			endforeach;
		endif;
		return $this;
	}
/**
 *	Show error, info or warning messages
 *	@param mixed $message The message(s) to be shown
 *	@param string $message_type e)rror, i)info, w)arning
 *	@return $this
 */
	private function ins_message_box($message,string $message_type = NULL) {
		global $g_img;

		$this->reset_hooks();
		if(is_string($message)):
			$grid = [$message];
		elseif(is_array($message)):
			$grid = $message;
		endif;
		if(is_array($grid)):
			switch($message_type):
				default:
					$id = 'errorbox';
					$src = $g_img['box.error'];
					$alt = '';
					break;
				case 'info':
					$id = 'infobox';
					$src = $g_img['box.info'];
					$alt = '';
					break;
				case 'warning':
					$id = 'warningbox';
					$src = $g_img['box.warning'];
					$alt = '';
					break;
			endswitch;
			$firstrowtrigger = true;
			foreach($grid as $rowvalue):
				if(is_string($rowvalue) && preg_match('/\S/',$rowvalue)):
					if($firstrowtrigger):
						$hook_id = $this->addDIV(['id' => $id]);
						$mbcl1 = $hook_id->addDIV(['class' => 'mbcl-1']);
						$this->add_hook($mbcl1,'mbcl-1');
						$mbcl2 = $mbcl1->addDIV(['class' => 'mbcl-2 mbci-min']);
						$mbcl2i1 = $mbcl2->addDIV(['class' => 'icon mbci-min']);
						$mbcl2i1->insIMG(['src' => $src,'alt' => $alt]);
						$mbcl2i2 = $mbcl2->addDIV(['class' => 'message mbci-max']);
						$hook_messages = $mbcl2i2->addDIV(['class' => 'mbcl-3 mbci-min']);
						$this->add_hook($hook_messages,'messages');
						$firstrowtrigger = false;
					endif;
					$hook_messages->insDIV([],htmlspecialchars_decode($rowvalue,ENT_QUOTES|ENT_HTML5));
//					$mbcl3->insDIV([],$rowvalue);
				endif;
			endforeach;
		endif;
		return $this;
	}
	public function ins_error_box($message = NULL) {
		return $this->ins_message_box($message,'error');
	}
	public function ins_info_box($message = NULL) {
		return $this->ins_message_box($message,'info');
	}
	public function ins_warning_box($message = NULL) {
		return $this->ins_message_box($message,'warning');
	}
	public function ins_config_save_message_box($errorcode) {
		global $d_sysrebootreqd_path;

		if($errorcode == 0):
			if(file_exists($d_sysrebootreqd_path)):
				$message = [
					gettext('The changes have been saved.'),
					sprintf('<a href="reboot.php">%s</a>',gettext('You have to reboot the system for the changes to take effect.'))
				];
			else:
				$message = gettext('The changes have been applied successfully.');
			endif;
		else:
			$message = sprintf('%s: %s (%s %s).',gettext('Error'),gettext('The changes could not be applied'),gettext('Error Code'),$errorcode);
		endif;
		$this->ins_info_box($message);
		return $this;
	}
	public function ins_config_has_changed_box() {
		$gt_info = [
			gettext('The configuration has been changed.'),
			gettext('You must apply the changes in order for them to take effect.'),
			sprintf('<a href="diag_log.php">%s</a>',gettext('If this message persists take a look at the system log for more information.'))
		];
		$this->addDIV(['id' => 'applybox'])->ins_info_box($gt_info);
		$hooks = $this->get_hooks();
		if(array_key_exists('mbcl-1',$hooks)):
			$hooks['mbcl-1']->addDIV(['class' => 'mbci-min'])->ins_button_apply();
		endif;
		return $this;
	}
	//	data settings table macros
	public function add_table_data_settings() {
		$subnode = $this->addTABLE(['class' => 'area_data_settings']);
		return $subnode;
	}
	public function ins_colgroup_data_settings() {
		$this->ins_colgroup_with_classes(['area_data_settings_col_tag','area_data_settings_col_data']);
		return $this;
	}
	public function add_table_data_selection() {
		$subnode = $this->addTABLE(['class' => 'area_data_selection']);
		return $subnode;
	}
	public function ins_colgroup_with_classes(array $data = []) {
		$colgroup = $this->addCOLGROUP();
		foreach($data as $value):
			$colgroup->insCOL(['class' => $value]);
		endforeach;
		return $this;
	}
	public function ins_colgroup_with_styles(string $tag,array $data = []) {
		$colgroup = $this->addCOLGROUP();
		foreach($data as $value):
			$colgroup->insCOL(['style' => sprintf('%s:%s;',$tag,$value)]);
		endforeach;
		return $this;
	}
	//	title macros
	public function ins_titleline(string $title = NULL,int $colspan = 0,string $id = NULL) {
		$tr_attributes = [];
		$th_attributes = [];
		if(!is_null($id) && preg_match('/\S/',$id)):
			$tr_attributes['id'] = sprintf('%s_tr',$id);
		endif;
		$th_attributes['class'] = 'lhetop';
		if($this->option_exists('tablesort')):
			$tr_attributes['class'] = 'tablesorter-ignoreRow';
		endif;
		if($colspan > 0):
			$th_attributes['colspan'] = $colspan;
		endif;
		$spanleft_attributes = ['style' => 'float:left'];
		$this->addTR($tr_attributes)->addTH($th_attributes)->addSPAN($spanleft_attributes,$title);
		return $this;
	}
	public function ins_titleline_with_checkbox($p,$value,bool $is_required = false,bool $is_readonly = false,string $title = '',int $colspan = 0) {
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$tr_attributes = [];
		$th_attributes = [];
		$tr_attributes['id'] = sprintf('%s_tr',$p->get_id());
		$th_attributes['class'] = 'lhetop';
		if($this->option_exists('tablesort')):
			$tr_attributes['class'] = 'tablesorter-ignoreRow';
		endif;
		if($colspan > 0):
			$th_attributes['colspan'] = $colspan;
		endif;
		$spanleft_attributes = ['style' => 'float:left'];
		$spanright_attributes = ['style' => 'float:right'];
		$input_attributes = [
			'type' => 'checkbox',
			'id' => $p->get_id(),
			'name' => $p->get_name(),
			'class' => 'formfld cblot',
			'value' => 'yes',
			'class' => 'oneemhigh'
		];
		if(isset($preset) && $preset):
			$input_attributes['checked'] = 'checked';
		endif;
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$span_attributes = ['class' => 'cblot'];
		$this->
			addTR($tr_attributes)->
				addTH($th_attributes)->
					insSPAN($spanleft_attributes,$title)->
					addSPAN($spanright_attributes)->
						addElement('label')->
							insINPUT($input_attributes)->
							addSPAN($span_attributes,$p->get_caption());
		return $this;
	}
	public function ins_description($p) {
		//	description can be:
		//	string
		//	[string, ...]
		//	[ [string], ...]
		//	[ [string,no_br], ...]
		//	[ [string,color], ...]
		//	[ [string,color,no_br], ...]
		$description = $p->get_description();
		if(isset($description)):
			$description_output = '';
			$suppressbr = true;
			if(!empty($description)): // string or array
				if(is_string($description)):
					$description_output = $description;
				elseif(is_array($description)):
					foreach($description as $description_row):
						if(is_string($description_row)):
							if($suppressbr):
								$description_output .= $description_row;
								$suppressbr = false;
							else:
								$description_output .= ('<br />' . $description_row);
							endif;
						elseif(is_array($description_row)):
							switch(count($description_row)):
								case 1:
									if(is_string($description_row[0])):
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										$description_output .= $description_row[0];
									endif;
									break;
								case 2:
									if(is_string($description_row[0])):
										$color = NULL;
										if(is_string($description_row[1])):
											$color = $description_row[1];
										endif;
										if(is_bool($description_row[1])):
											$suppressbr = $description_row[1];
										endif;
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										if(is_null($color)):
											$description_output .= $description_row[0];
										else:
											$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
										endif;
									endif;
									break;
								case 3: // allow not to break
									if(is_string($description_row[0])):
										$color = NULL;
										if(is_string($description_row[1])):
											$color = $description_row[1];
										endif;
										if(is_bool($description_row[2])):
											$suppressbr = $description_row[2];
										endif;
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										if(is_null($color)):
											$description_output .= $description_row[0];
										else:
											$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
										endif;
									endif;
									break;
							endswitch;
						endif;
					endforeach;
				endif;
			endif;
			if(preg_match('/\S/',$description_output)):
				$this->addDIV(['class' => 'formfldadditionalinfo'],$description_output);
			endif;
		endif;
		return $this;
	}
	public function ins_checkbox($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->reset_hooks();
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$id = $p->get_id();
		$input_attributes = [
			'type' => 'checkbox',
			'id' => $id,
			'name' => $p->get_name(),
			'value' => 'yes',
			'class' => 'oneemhigh'
		];
		if(isset($preset) && $preset):
			$input_attributes['checked'] = 'checked';
		endif;
		if($is_readonly):
			$class_checkbox = 'celldatacheckbox';
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		else:
			$class_checkbox = 'celldatacheckbox';
		endif;
		if($is_required):
			$class_checkbox = 'celldatacheckbox';
			$input_attributes['required'] = 'required';
		endif;
		$hook = $this->addDIV(['class' => $class_checkbox])->addELEMENT('label',['for' => $id]);
		$hook->insINPUT($input_attributes);
		$hook->import_soup($p->get_caption());
		$this->add_hook($hook,$id);
		return $this;
	}
	public function ins_input($p,$value,bool $is_required = false,bool $is_readonly = false,int $type = 0) {
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$id = $p->get_id();
		$caption = $p->get_caption();
		$input_attributes = [
			'id' => $id,
			'name' => $p->get_name(),
			'value' => $preset
		];
		switch($type):
			case 0:
				$input_attributes['type'] = 'text';
				break;
			case 1:
				$input_attributes['type'] = 'password';
				break;
		endswitch;
		if($is_readonly):
			$input_attributes['class'] = 'formfldro';
			$input_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $p->get_placeholderv();
		else:
			$input_attributes['class'] = 'formfld';
			$maxlength = $p->get_maxlength();
			$placeholder = $p->get_placeholder();
		endif;
		if($is_required):
			$input_attributes['class'] = 'formfld';
			$input_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$input_attributes['placeholder'] = $placeholder;
		endif;
		$size = $p->get_size();
		if($size > 0):
			$input_attributes['size'] = $size;
		endif;
		if($maxlength > 0):
			$input_attributes['maxlength'] = $maxlength;
		endif;
		$div = $this->addDIV();
		$div->insINPUT($input_attributes);
		if(isset($caption)):
			if($is_readonly):
				$div->insSPAN(['style' => 'margin-left: 0.7em;'],$caption);
			else:
				$div->addElement('label',['style' => 'margin-left: 0.7em;','for' => $id],$caption);
			endif;
		endif;
		return $this;
	}
	public function ins_input_hidden(string $name = NULL,$value = '') {
		if(isset($name) && preg_match('/\S/',$name) && is_scalar($value)):
			$input_attributes = ['type' => 'hidden'];
			if(preg_match('/\S/',$name)):
				$input_attributes['name'] = $name;
			endif;
			if(is_scalar($value)):
				$input_attributes['value'] = $value;
			endif;
			$this->addDIV()->insINPUT($input_attributes);
		endif;
		return $this;
	}
	public function ins_checkbox_grid($p,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->reset_hooks();
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$table = $this->add_table_data_selection();
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY();
		$input_attributes = [
			'name' => sprintf('%s[]',$p->get_name()),
			'type' => 'checkbox',
			'class' => 'oneemhigh'
		];
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$n_options = 0;
		foreach($p->get_options() as $option_key => $option_val):
			$option_tag = (string)$option_key;
			$input_attributes['value'] = $option_tag;
			$input_attributes['id'] = sprintf('checkbox_%s',uuid());
			if(is_array($preset) && in_array($option_tag,$preset)):
				$input_attributes['checked'] = 'checked';
			elseif(array_key_exists('checked',$input_attributes)):
				unset($input_attributes['checked']);
			endif;
			$hook = $tbody->addTR()->addTDwC('lcebl')->addELEMENT('label',['for' => $input_attributes['id']]);
			$hook->insINPUT($input_attributes);
			$hook->import_soup($option_val);
			$this->add_hook($hook,$option_tag);
			$n_options++;
		endforeach;
		switch($n_options <=> 1):
			case -1:
				$message_info = $p->get_message_info();
				if(!is_null($message_info)):
					$table->addTFOOT()->addTR()->addTDwC('lcebl',$message_info);
				endif;
				$suppress_tablesort = $this->option_exists('tablesort');
				break;
			case 0:
				$suppress_tablesort = $this->option_exists('tablesort');
				break;
			case 1:
				$suppress_tablesort = ($this->option_exists('tablesort') && !$use_tablesort);
				break;
		endswitch;
		if($suppress_tablesort):
			$tr = $thead->addTR(['class' => 'tablesorter-ignoreRow']);
		else:
			$tr = $thead->addTR();
		endif;
		$tr->insTHwC('lhebl',$p->get_title());
		return $this;
	}
	public function ins_filechooser($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$id = $p->get_id();
		$name = $p->get_name();
		$input_attributes = [
			'type' => 'text',
			'id' => $id,
			'name' => $name,
			'value' => $preset
		];
		if($is_readonly):
			$input_attributes['class'] = 'formfldro';
			$input_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $p->get_placeholderv();
		else:
			$input_attributes['class'] = 'formfld';
			$maxlength = $p->get_maxlength();
			$placeholder = $p->get_placeholder();
		endif;
		if($is_required):
			$input_attributes['class'] = 'formfld';
			$input_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$input_attributes['placeholder'] = $placeholder;
		endif;
		$size = $p->get_size();
		if($size > 0):
			$input_attributes['size'] = $size;
		endif;
		if($maxlength > 0):
			$input_attributes['maxlength'] = $maxlength;
		endif;
		$div = $this->addDIV();
		$div->insINPUT($input_attributes);
//	file chooser start
		if(!$is_readonly):
			$var = 'ifield';
			$idifield = sprintf('%1$s%2$s',$id,$var);
			$js = <<<EOJ
{$idifield} = form.{$id};
filechooser = window.open("filechooser.php?p="+encodeURIComponent({$idifield}.value)+"&sd={$preset}","filechooser","scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");
filechooser.{$var} = {$idifield};
window.{$var} = {$idifield};
EOJ;
			$button_attributes = [
				'type' => 'button',
				'id' => $id . 'browsebtn',
				'name' => $name . 'browsebtn',
				'class' => 'formbtn',
				'onclick' => $js,
				'value' => '...'
			];
			$div->insINPUT($button_attributes);
		endif;
//	file chooser end
		$caption = $p->get_caption();
		if(isset($caption)):
			if($is_readonly):
				$div->insSPAN(['style' => 'margin-left: 0.7em;'],$caption);
			else:
				$div->addElement('label',['style' => 'margin-left: 0.7em;','for' => $p->get_id()],$p->get_caption());
			endif;
		endif;
		return $this;
	}
	public function ins_radio_grid($p,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->reset_hooks();
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$table = $this->add_table_data_selection();
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY();
		$input_attributes = [
			'name' => $p->get_name(),
			'type' => 'radio',
			'class' => 'oneemhigh'
		];
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$n_options = 0;
		foreach($p->get_options() as $option_key => $option_val):
			$option_tag = (string)$option_key;
			$input_attributes['value'] = $option_tag;
			$input_attributes['id'] = sprintf('radio_%s',uuid());
			if($preset === $option_tag):
				$input_attributes['checked'] = 'checked';
			elseif(array_key_exists('checked',$input_attributes)):
				unset($input_attributes['checked']);
			endif;
			$hook = $tbody->addTR()->addTDwC('lcebl')->addELEMENT('label',['for' => $input_attributes['id']]);
			$hook->insINPUT($input_attributes);
			$hook->import_soup($option_val);
			$this->add_hook($hook,$option_tag);
			$n_options++;
		endforeach;
		switch($n_options <=> 1):
			case -1:
				$message_info = $p->get_message_info();
				if(!is_null($message_info)):
					$table->addTFOOT()->addTR()->addTDwC('lcebl',$message_info);
				endif;
				$suppress_tablesort = $this->option_exists('tablesort');
				break;
			case 0:
				$suppress_tablesort = $this->option_exists('tablesort');
				break;
			case 1:
				$suppress_tablesort = ($this->option_exists('tablesort') && !$use_tablesort);
				break;
		endswitch;
		if($suppress_tablesort):
			$tr = $thead->addTR(['class' => 'tablesorter-ignoreRow']);
		else:
			$tr = $thead->addTR();
		endif;
		$tr->insTHwC('lhebl',$p->get_title());
		return $this;
	}
	public function ins_select($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$caption = $p->get_caption();
		$select_attributes = [
			'id' => $p->get_id(),
			'name' => $p->get_name(),
			'class' => 'formfld'
		];
		if($is_readonly):
			$select_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$select_attributes['required'] = 'required';
		endif;
		$select = $this->addElement('select',$select_attributes);
		if($is_required):
			$select->addElement('option',['value' => ''],gettext('Choose...'));
		endif;
		foreach($p->get_options() as $option_tag => $option_val):
			$option_attributes = ['value' => $option_tag];
			if($preset == $option_tag):
				$option_attributes['selected'] = 'selected';
			endif;
			$select->addElement('option',$option_attributes,$option_val);
		endforeach;
		if(isset($caption)):
			$this->insSPAN(['style' => 'margin-left: 0.7em;'],$caption);
		endif;
		return $this;
	}
	public function ins_separator(int $colspan = 0,string $id = NULL) {
		$tr_attributes = [];
		if($this->option_exists('tablesort')):
			$tr_attributes = ['class' => 'tablesorter-ignoreRow'];
		endif;
		if(isset($id) && preg_match('/\S/',$id)):
			$tr_attributes['id'] = sprintf('%s_tr',$id);
		endif;
		$td_attributes = [
			'class' => 'gap'
		];
		if($colspan > 0):
			$td_attributes['colspan'] = $colspan;
		endif;
		$this->addTR($tr_attributes)->addTD($td_attributes);
		return $this;
	}
	public function ins_textarea($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$preset = is_object($value) ? $value->row[$p->get_name()] : $value;
		$id = $p->get_id();
		$caption = $p->get_caption();
		$textarea_attributes = [
			'id' => $id,
			'name' => $p->get_name(),
		];
		if($is_readonly):
			$textarea_attributes['class'] = 'formprero';
			$textarea_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $p->get_placeholderv();
		else:
			$textarea_attributes['class'] = 'formpre';
			$maxlength = $p->get_maxlength();
			$placeholder = $p->get_placeholder();
		endif;
		if($is_required):
			$textarea_attributes['class'] = 'formpre';
			$textarea_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$textarea_attributes['placeholder'] = $placeholder;
		endif;
		$n_cols = $p->get_cols();
		if($n_cols > 0):
			$textarea_attributes['cols'] = $n_cols;
		endif;
		$n_rows = $p->get_rows();
		if($n_rows > 0):
			$textarea_attributes['rows'] = $n_rows;
		endif;
		$textarea_attributes['wrap'] = $p->get_wrap() ? 'hard' : 'soft';
		if($maxlength > 0):
			$textarea_attributes['maxlength'] = $maxlength;
		endif;
		$div = $this->addDIV();
		$div->addElement('textarea',$textarea_attributes,$preset);
		if(isset($caption)):
			if($is_readonly):
				$div->insSPAN(['style' => 'margin-left: 0.7em;'],$caption);
			else:
				$div->addElement('label',['style' => 'margin-left: 0.7em;','for' => $id],$caption);
			endif;
		endif;
		return $this;
	}
	public function ins_textinfo(string $id = NULL,string $value = NULL) {
		if(isset($value)):
			$span_attributes = [];
			if(isset($id)):
				$span_attributes = ['id' => $id];
			endif;
			$this->insSPAN($span_attributes,$value);
		endif;
		return $this;
	}
	//	elements requiring sphere
	public function ins_cbm_checkbox_toggle($sphere) {
		$cbm_toggle_id = $sphere->get_cbm_checkbox_id_toggle();
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $cbm_toggle_id,
			'id' => $cbm_toggle_id,
			'title' => gettext('Invert Selection'),
			'class' => 'oneemhigh'
		];
		$this->insINPUT($input_attributes);
		return $this;
	}
	public function ins_cbm_checkbox($sphere,bool $disabled = false) {
		$identifier = $sphere->get_row_identifier_value();
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $sphere->get_cbm_name() . '[]',
			'value' => $identifier,
			'id' => $identifier,
			'class' => 'oneemhigh'
		];
		if($disabled):
			$input_attributes['disabled'] = 'disabled';
		endif;
		$this->insINPUT($input_attributes);
		return $this;
	}
	public function ins_enadis_icon(bool $is_enabled = false) {
		global $g_img;

		if($is_enabled):
			$gt = gettext('Enabled');
			$this->
				addTDwC('lcelc')->
					addA(['title' => $gt])->
						insIMG(['src' => $g_img['ena'],'alt' => $gt]);
		else:
			$gt = gettext('Disabled');
			$this->
				addTDwC('lcelcd')->
					addA(['title' => $gt])->
						insIMG(['src' => $g_img['dis'],'alt' => $gt]);
		endif;
		return $this;
	}
	public function add_toolbox_area() {
		$subnode = $this->
			addTDwC('lcebld')->
				addTABLE(['class' => 'area_data_selection_toolbox'])->
					ins_colgroup_with_styles('width',['33%','34%','33%'])->
					addTBODY()->
						addTR();
		return $subnode;
	}
	public function ins_toolbox($sphere,bool $notprotected = true,bool $notdirty = true) {
		global $g_img;
/*
 *	<td>
 *		<a href="scriptname_edit.php?submit=edit&uuid=12345678-1234-1234-1234-1234567890AB"><img="images/edit.png" title="Edit Record" alt="Edit Record" class="spin"/></a>
 *		or
 *		<img src="images/delete.png" title="Record is marked for deletion" alt="Record is marked for deletion"/>
 *		or
 *		<img src="images/locked.png" title="Record is protected" alt="Record is protected"/>
 *	</td>
 */
		if($notdirty && $notprotected): // record is editable
			$querystring = http_build_query(['submit' => 'edit',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],NULL,ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_modify()->get_scriptname(),$querystring);
			$this->addTD()->
				addA(['href' => $link])->
					insIMG(['src' => $g_img['mod'],'title' => $sphere->getmsg_sym_mod(),'alt' => $sphere->getmsg_sym_mod(),'class' => 'spin oneemhigh']);
		elseif($notprotected): //record is dirty
			$this->addTD()->
				insIMG(['src' => $g_img['del'],'title' => $sphere->getmsg_sym_del(),'alt' => $sphere->getmsg_sym_del(),'class' => 'oneemhigh']);
		else: // record is protected
			$this->addTD()->
				insIMG(['src' => $g_img['loc'],'title' => $sphere->getmsg_sym_loc(),'alt' => $sphere->getmsg_sym_loc(),'class' => 'oneemhigh']);
		endif;
		return $this;
	}
	public function ins_maintainbox($sphere,bool $show_link = false) {
		global $g_img;
/*
 *	<td>
 *		<a href="scriptname_maintain.php?submit=maintain&uuid=12345678-1234-1234-1234-1234567890AB"><img="images/maintain.png" title="Maintenance" alt="Maintenance" class="spin oneemhigh"/></a>
 *	</td>
 */
		$td = $this->addTD();
		if($show_link): // show link
			$querystring = http_build_query(['submit' => 'maintain',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],NULL,ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_maintain()->get_scriptname(),$querystring);
			$td->addA(['href' => $link])->insIMG(['src' => $g_img['mai'],'title' => $sphere->getmsg_sym_mai(),'alt' => $sphere->getmsg_sym_mai(),'class' => 'spin oneemhigh']);
		endif;
		return $this;
	}
	public function ins_informbox($sphere,bool $show_link = false) {
		global $g_img;
/*
 *	<td>
 *		<a href="scriptname_inform.php?submit=inform&uuid=12345678-1234-1234-1234-1234567890AB"><img="images/info.png" title="Information" alt="Information" class="spin oneemhigh"/></a>
 *	</td>
 */
		$td = $this->addTD();
		if($show_link): // show link
			$querystring = http_build_query(['submit' => 'inform',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],NULL,ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_inform()->get_scriptname(),$querystring);
			$td->addA(['href' => $link])->insIMG(['src' => $g_img['inf'],'title' => $sphere->getmsg_sym_inf(),'alt' => $sphere->getmsg_sym_inf(),'class' => 'spin oneemhigh']);
		endif;
		return $this;
	}
	public function ins_updownbox($sphere,bool $show_arrows = false) {
		global $g_img;

		$td = $this->addTD();
		if($show_arrows): // show up and down arrows
			$image_attribute_mup = [
				'src' => $g_img['mup'],
				'title' => $sphere->getmsg_sym_mup(),
				'alt' => $sphere->getmsg_sym_mup(),
				'class' => 'oneemhigh move up'
			];
			$image_attribute_mdn = [
				'src' => $g_img['mdn'],
				'title' => $sphere->getmsg_sym_mdn(),
				'alt' => $sphere->getmsg_sym_mdn(),
				'class' => 'oneemhigh move down'
			];
			$td->insIMG($image_attribute_mup)->insIMG($image_attribute_mdn);
		endif;
		return $this;
	}
	public function ins_record_add($sphere,int $colspan = 0) {
		global $g_img;
/*
 *	<tr>
 *		<th class="lcenl" colspan="1"></th>
 *		<th class="lceadd">
 *			<a href="scriptname_edit.php?submit=add">
 *				<img src="images/add.png" title="Add Record" alt="Add Record" class="spin oneemhigh"/>
 *			</a>
 *		</th>
 *	</tr>
 */
		$querystring = http_build_query(['submit' => 'add'],NULL,ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
		$link = sprintf('%s?%s',$sphere->get_modify()->get_scriptname(),$querystring);
		//	PHP_QUERY_RFC3986
		$tr = $this->addTR();
		if($colspan > 1):
			$tr->addTH(['class' => 'lcenl','colspan' => $colspan - 1]);
		endif;
		$tr->
			addTHwC('lceadd')->
				addA(['href' => $link])->
					insIMG(['src' => $g_img['add'],'title' => $sphere->getmsg_sym_add(),'alt' => $sphere->getmsg_sym_add(),'class' => 'spin oneemhigh']);
		return $this;
	}
	public function ins_no_records_found(int $colspan = 0,string $message = NULL) {
		if(is_null($message)):
			$message = gettext('No records found.');
		endif;
		$td_attributes = ['class' => 'lcebl'];
		if($colspan > 0):
			$td_attributes['colspan'] = $colspan;
		endif;
		$this->addTR()->addTD($td_attributes,$message);
		return $this;
	}
	public function ins_cbm_button_delete($sphere) {
		$this->ins_button_submit($sphere->get_cbm_button_val_delete(),$sphere->getmsg_cbm_delete(),[],$sphere->get_cbm_button_id_delete());
		return $this;
	}
	public function ins_cbm_button_enadis($sphere) {
		if($sphere->is_enadis_enabled()):
			if($sphere->toggle()):
				$this->ins_button_submit($sphere->get_cbm_button_val_toggle(),$sphere->getmsg_cbm_toggle(),[],$sphere->get_cbm_button_id_toggle());
			else:
				$this->ins_button_submit($sphere->get_cbm_button_val_enable(),$sphere->getmsg_cbm_enable(),[],$sphere->get_cbm_button_id_enable());
				$this->ins_button_submit($sphere->get_cbm_button_val_disable(),$sphere->getmsg_cbm_disable(),[],$sphere->get_cbm_button_id_disable());
			endif;
		endif;
		return $this;
	}
	//	c2 blocks
	public function c2_row($p,bool $is_required = false,bool $is_readonly = false,bool $tagaslabel = false) {
		if($is_readonly):
			$class_tag = 'celltag';
			$class_data = 'celldata';
			$is_required = false;
		else:
			$class_tag = 'celltag';
			$class_data = 'celldata';
		endif;
		if($is_required):
			$class_tag = 'celltagreq';
			$class_data = 'celldatareq';
		endif;
		$tr = $this->addTR(['id' => sprintf('%s_tr',$p->get_id())]);
		if($tagaslabel):
			$tr->addTDwC($class_tag)->addElement('label',['for' => $p->get_id()],$p->get_title());
		else:
			$tr->addTDwC($class_tag,$p->get_title());
		endif;
		$subnode = $tr->addTDwC($class_data);
		return $subnode;
	}
	public function c2_checkbox($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_checkbox($p,$value,$is_required,$is_readonly)->
				ins_description($p);
		return $this;
	}
	public function c2_checkbox_grid($p,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,false)->
				ins_checkbox_grid($p,$value,$is_required,$is_readonly,$use_tablesort)->
				ins_description($p);
		return $this;
	}
	public function c2_filechooser($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_filechooser($p,$value,$is_required,$is_readonly)->
				ins_description($p);
		return $this;
	}
	public function c2_input_text($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_input($p,$value,$is_required,$is_readonly,0)->
				ins_description($p);
		return $this;
	}
	public function c2_input_password($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_input($p,$value,$is_required,$is_readonly,1)->
				ins_description($p);
		return $this;
	}
	public function c2_radio_grid($p,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,false)->
				ins_radio_grid($p,$value,$is_required,$is_readonly,$use_tablesort)->
				ins_description($p);
		return $this;
	}
	public function c2_select($p,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_select($p,$value,$is_required,$is_readonly)->
				ins_description($p);
		return $this;
	}
	public function c2_separator() {
		$this->ins_separator(2);
		return $this;
	}
	public function c2_textarea($p,$value,bool $is_required = false,bool $is_readonly = false,int $n_cols = 0,int $n_rows = 0) {
		if($n_cols > 0):
			$p->set_cols($n_cols);
		endif;
		if($n_rows > 0):
			$p->set_rows($n_rows);
		endif;
		$this->
			c2_row($p,$is_required,$is_readonly,true)->
				ins_textarea($p,$value,$is_required,$is_readonly)->
				ins_description($p);
		return $this;
	}
	public function c2_textinfo(string $id,string $title,$value) {
		$tr_attributes = [];
		if(isset($id)):
			$tr_attributes['id'] = sprintf('%s_tr',$id);
		endif;
		$tr = $this->addTR($tr_attributes);
		$tr->addTDwC('celltag',$title);
		$tr->addTDwC('celldata')->ins_textinfo($id,$value);
		return $this;
	}
	public function c2_titleline(string $title = '') {
		$this->ins_titleline($title,2);
		return $this;
	}
	public function c2_titleline_with_checkbox($p,$value,bool $is_required = false,bool $is_readonly = false,string $title = '') {
		$this->ins_titleline_with_checkbox($p,$value,$is_required,$is_readonly,$title,2);
		return $this;
	}
	//	submit area macros
	public function add_area_buttons(bool $use_config_setting = true) {
		global $config;

		$div_attributes = ['id' => 'submit'];
		if($use_config_setting):
			$root = $this->ownerDocument ?? $this;
			if(is_bool($test = $config['system']['adddivsubmittodataframe'] ?? false) ? $test : true):
				$target = $root->getElementById('area_data_frame') ?? $this;
				$subnode = $target->addDIV($div_attributes);
			else:
				$target = $root->getElementById('g4f') ?? $this;
				$div_attributes['style'] = 'padding: 0em 2em;';
				$subnode = $target->prepend_element('div',$div_attributes);
			endif;
		else:
			$subnode = $this->addDIV($div_attributes);
		endif;
		return $subnode;
	}
	public function ins_button_submit(string $value = NULL,string $content = NULL,array $attributes = NULL,string $id = NULL) {
		$element = 'button';
		$class_button = 'formbtn';
		$sp_value = $value ?? 'cancel';
		$sp_content = $content ?? gettext('Cancel');
		$sp_attributes = $attributes ?? [];
		$sp_id  = $id ?? sprintf('%1$s_%2$s',$element,$sp_value);
		$button_attributes  = [
			'name' => 'submit',
			'type' => 'submit',
			'class' => $class_button,
			'value' => $sp_value,
			'id' => $sp_id,
			'title' => $sp_content
		];
		foreach($sp_attributes as $key_attribute => $val_attribute):
			switch($key_attribute):
				case 'class+':
					$button_attributes['class'] += ' ' . $val_attribute;
					break;
				default:
					$button_attributes[$key_attribute] = $val_attribute;
					break;
			endswitch;
		endforeach;
		$this->addElement($element,$button_attributes,$sp_content);
		return $this;
	}
	public function ins_button_add(string $content = NULL) {
		$this->ins_button_submit('save',$content ?? gettext('Add'));
		return $this;
	}
	public function ins_button_apply(string $content = NULL) {
		$this->ins_button_submit('apply',$content ?? gettext('Apply Changes'));
		return $this;
	}
	public function ins_button_cancel(string $content = NULL) {
		$this->ins_button_submit('cancel',$content ?? gettext('Cancel'),['formnovalidate' => 'formnovalidate']);
		return $this;
	}
	public function ins_button_clone(string $content = NULL) {
		$this->ins_button_submit('clone',$content ?? gettext('Clone Configuration'));
		return $this;
	}
	public function ins_button_edit(string $content = NULL) {
		$this->ins_button_submit('edit',$content ?? gettext('Edit'));
		return $this;
	}
	public function ins_button_enadis(bool $enable = false,string $content_on = NULL,string $content_off = NULL) {
		if($enable):
			$this->ins_button_submit('enable',$content_on ?? gettext('Enable'));
		else:
			$this->ins_button_submit('disable',$content_off ?? gettext('Disable'));
		endif;
		return $this;
	}
	public function ins_button_reload(bool $enable = false,string $content = NULL) {
		if($enable):
			$this->ins_button_submit('reload',$content ?? gettext('Reload'));
		endif;
		return $this;
	}
	public function ins_button_reorder(bool $enable = false,string $content = NULL) {
		if($enable):
			$this->ins_button_submit('reorder',$content ?? gettext('Reorder'));
		endif;
		return $this;
	}
	public function ins_button_rescan(bool $enable = false,string $content = NULL) {
		if($enabled):
			$this->ins_button_submit('rescan',$content ?? gettext('Rescan'));
		endif;
		return $this;
	}
	public function ins_button_restart(bool $enable = false,string $content = NULL) {
		if($enable):
			$this->ins_button_submit('restart',$content ?? gettext('Restart'));
		endif;
		return $this;
	}
	public function ins_button_save(string $content = NULL) {
		$this->ins_button_submit('save',$content ?? gettext('Apply'));
		return $this;
	}
	//	remark area macros
	public function add_area_remarks() {
		$subnode = $this->addDIV(['id' => 'remarks']);
		return $subnode;
	}
	public function ins_remark($ctrlname,$title,$text) {
		$this->addDIV(['id' => $ctrlname])->addElement('strong',['class' => 'red'],$title);
		$this->addDIV([],$text);
		return $this;
	}
	public function ins_authtoken() {
		$input_attributes = [
			'name' => 'authtoken',
			'type' => 'hidden',
			'value' => Session::getAuthToken()
		];
		$this->insINPUT($input_attributes);
		return $this;
	}
	public function clc_page_title(array $page_title = []) {
		$output = implode(' > ',$page_title);
		return $output;
	}
	public function clc_html_page_title(array $page_title = []) {
		$output = system_get_hostname();
		if(!empty($page_title)):
			$output .= ' > ';
			$output .= $this->clc_page_title($page_title);
		endif;
		return $output;
	}
	public function ins_head(array $page_title = []) {
		$head = $this->addElement('head',['id' => 'head']);
		$head->
			insElement('meta',['charset' => system_get_language_codeset()])->
			insElement('meta',['name' => 'format-detection','content' => 'telephone=no'])->
			insElement('meta',['name' => 'viewport','content' => 'width=device-width, initial-scale=1.0'])->
			insElement('meta',['name' => 'robots','content' => 'noindex,nofollow'])->
			insElement('title',[],$this->clc_html_page_title($page_title))->
			insElement('link',['href' => '/css/gui.css.php','rel' => 'stylesheet','type' => 'text/css'])->
			insElement('link',['href' => '/css/navbar.css.php','rel' => 'stylesheet','type' => 'text/css'])->
			insElement('link',['href' => '/css/tabs.css.php','rel' => 'stylesheet','type' => 'text/css']);
		if($this->option_exists('login')):
			$head->
				insElement('link',['href' => '/css/login.css.php','rel' => 'stylesheet','type' => 'text/css']);
		endif;
		$head->
			insElement('style',[],'.avoid-fouc { display:none; }');
		$head->
			addJavaScript("document.documentElement.className = 'avoid-fouc';");
		$head->
			insElement('script',['src' => '/js/jquery.min.js'])->
			insElement('script',['src' => '/js/gui.js'])->
			insElement('script',['src' => '/js/spinner.js'])->
			insElement('script',['src' => '/js/spin.min.js']);
		if($this->option_exists('tablesort')):
			$head->insElement('script',['src' => '/js/jquery.tablesorter.min.js']);
			if($this->option_exists('tablesort-widgets')):
				$head->insElement('script',['src' => '/js/jquery.tablesorter.widgets.min.js']);
			endif;
			if($this->option_exists('sorter-bytestring')):
				$head->insElement('script',['src' => '/js/parser-bytestring.js']);
			endif;
			if($this->option_exists('sorter-checkbox')):
				$head->insElement('script',['src' => '/js/parser-checkbox.js']);
			endif;
			if($this->option_exists('sorter-radio')):
				$head->insElement('script',['src' => '/js/parser-radio.js']);
			endif;
		endif;
		if($this->option_exists('datechooser')):
			$head->
				insElement('link',['href' => 'js/datechooser.css','rel' => 'stylesheet','type' => 'text/css'])->
				insElement('script',['src' => 'js/datechooser.js']);
		endif;
		$head->
			insElement('link',['href' => '/images/info_box.png','rel' => 'prefetch','as' => 'image'])->
			insElement('link',['href' => '/images/warn_box.png','rel' => 'prefetch','as' => 'image'])->
			insElement('link',['href' => '/images/error_box.png','rel' => 'prefetch','as' => 'image']);
		return $this;
	}
	/**
	 *	Creates the body element of the page with all basic subnodes.
	 *
	 *	@param array $page_title
	 *	@param string $action_url If $action_url empty no form element will be created.
	 *	@return DOMNode $this
	 */
	public function ins_body(array $page_title = [],string $action_url = NULL) {
		$is_login = $this->option_exists('login');
		$is_multipart = $this->option_exists('multipart');
		$is_tablesort = $this->option_exists('tablesort');
		$is_form = (isset($action_url) && preg_match('/^\S+$/',$action_url));
		$is_spinonsubmit = $is_form && !$this->option_exists('nospinonsubmit');
		$is_tabnav = !($is_login || $this->option_exists('notabnav'));
		$body = $this->addElement('body',['id' => 'main']);
		if($is_form):
			$form_attributes = [
				'action' => $action_url,
				'method' => 'post',
				'id' => 'iform',
				'name' => 'iform'
			];
			if($is_multipart):
				$form_attributes['enctype'] = 'multipart/form-data';
			endif;
			$flexcontainer = $body->addFORM($form_attributes)->addDIV(['id' => 'pagebodyflex']);
		else:
			$flexcontainer = $body->addDIV(['id' => 'pagebodyflex']);
		endif;
		$flexcontainer->addDIV(['id' => 'spinner_main']);
		$flexcontainer->addDIV(['id' => 'spinner_overlay','style' => 'display: none; background-color: white; position: fixed; left:0; top:0; height:100%; width:100%; opacity: 0.25;']);
		if(!$is_login && $is_form):
			$flexcontainer->addDIV(['id' => 'formextension'])->ins_authtoken();
		endif;
		if($is_login):
			$flexcontainer->ins_header_login();
		else:
			$flexcontainer->ins_header_logo();
			$flexcontainer->ins_header($page_title);
		endif;
		$flexcontainer->ins_main();
		$flexcontainer->ins_footer();
		if($is_tabnav):
			$jdata = <<<'EOJ'
	$(".spin,#tabnav,#tabnav2").click(function() { spinner(); });
EOJ;
		else:
			$jdata = <<<'EOJ'
	$(".spin").click(function() { spinner(); });
EOJ;
		endif;
		$this->add_js_on_load($jdata);
		if($is_spinonsubmit):
			$jdata = <<<'EOJ'
	$("#iform").submit(function() { spinner(); });
EOJ;
			$this->add_js_on_load($jdata);
		endif;
		if(true):
			$jdata = <<<'EOJ'
	$('.avoid-fouc').removeClass('avoid-fouc');
EOJ;
			$this->add_js_document_ready($jdata);
		endif;
		if($is_tablesort):
			$jdata = <<<'EOJ'
	$.tablesorter.defaults.textSorter = $.tablesorter.sortText;
	$(".area_data_selection").tablesorter({
		emptyTo: 'none'
	});
EOJ;
			$this->add_js_document_ready($jdata);
		endif;
		return $this;
	}
	public function ins_header_logo() {
		if(!$_SESSION['g']['shrinkpageheader']):
			$a_attributes = [
				'title' => sprintf('www.%s',get_product_url()),
				'href' => sprintf('https://www.%s',get_product_url()),
				'target' => '_blank'
			];
			$img_attributes = [
				'src' => '/images/header_logo.png',
				'alt' => 'logo'
			];
			$this->addElement('header',['id' => 'g4l'])->
				addDIV(['id' => 'header'])->
					push()->
					addDIV(['id' => 'headerrlogo'])->
						addDIV(['class' => 'hostname'])->
							addSPAN([],system_get_hostname())->
					pop()->
					addDIV(['id' => 'headerlogo'])->
						addA($a_attributes)->
							insIMG($img_attributes);
		endif;
		return $this;
	}
	public function ins_header_login() {
		$header = $this->
			addElement('header',['id' => 'g4h'])->
				addDIV(['id' => 'gapheader']);
		return $this;
	}
	public function ins_header_menu() {
		$hard_link_regex = '~^[a-z]+://~';
		$menu = get_headermenu();
		make_headermenu_extensions($menu); // function cares about access rights itself
		$menu_list = ['home','system','network','disks','access','services','vm','status','diagnostics','extensions','tools','help'];
		$ul_h = $this->addDIV(['id' => 'area_navhdr'])->addElement('nav',['id' => 'navhdr'])->addUL();
		foreach($menu_list as $menuid):
			if($menu[$menuid]['visible']): // render menu when visible
				$li_h = $ul_h->addLI();
				$attributes = [];
				switch($menu[$menuid]['type']):
					case 'external':
						$attributes['href'] = $menu[$menuid]['link'];
						$attributes['target'] = '_blank';
						break;
					case 'internal':
						$attributes['href'] = $menu[$menuid]['link'];
						$attributes['onclick'] = 'spinner()';
						break;
					case 'nolink':
						$attributes['onclick'] = '';
						break;
				endswitch;
//				$tags = implode(' ',$a_tag);
				if(empty($menu[$menuid]['img'])):
					$li_h->
						addA($attributes,$menu[$menuid]['description']);
				else:
					$li_h->
						addA($attributes)->insIMG(['src' => $menu[$menuid]['img'],'title' => $menu[$menuid]['description'],'alt' => $menu[$menuid]['description']]);
				endif;
				if(!empty($menu[$menuid]['menuitem'])):
					$ul_v = $li_h->addUL();
					// Display menu items.
					foreach($menu[$menuid]['menuitem'] as $menu_item):
						if($menu_item['visible']): // render menuitem when visible
							$li_v = $ul_v->addLI();
							$target = ('internal' === $menu_item['type']) ? '_self' : '_blank';
							if('separator' === $menu_item['type']):
								$li_v->insSPAN(['class' => 'tabseparator']);
							else:
								$link = $menu_item['link'];
								if(preg_match($hard_link_regex,$link)): // hard link = no spinner
									$li_v->insA(['href' => $link,'target' => $target,'onclick' => ''],$menu_item['description']);
								else: // local link = spinner
									$li_v->insA(['href' => $link,'target' => $target,'onclick' => 'spinner()'],$menu_item['description']);
								endif;
							endif;
						endif;
					endforeach;
				endif;
			endif;
		endforeach;
		return $this;
	}
	public function ins_header(array $page_title = []) {
		$header = $this->addElement('header',['id' => 'g4h']);
		$header->ins_header_menu();
		$header->addDIV(['id' => 'gapheader']);
		if(!empty($page_title)):
			$header->addP(['class' => 'pgtitle','style' => 'padding:0em 2em;' ],$this->clc_page_title($page_title));
		endif;
		return $this;
	}
	public function ins_main() {
		$this->
			addElement('main',['id' => 'g4m'])->
				addDIV(['id' => 'pagecontent']);
		return $this;
	}
	/**
	 *
	 * @global string $d_sysrebootreqd_path
	 * @return $this
	 */
	public function ins_footer() {
		global $d_sysrebootreqd_path;

		$g4fx = $this->
			addElement('footer',['id' => 'g4f'])->
				insDIV(['id' => 'gapfooter'])->
				addDIV(['id' => 'pagefooter'])->
					add_table_data_settings()->
						ins_colgroup_with_styles('width',['10%','80%','10%'])->
						addTBODY()->
							addTR();
		$g4fl = $g4fx->addTDwC('g4fl');
		if(Session::isAdmin()):
			if(file_exists($d_sysrebootreqd_path)):
				$img_attributes = [
					'src' => '/images/notify_reboot.png',
					'title' => gettext('A reboot is required'),
					'alt' => gettext('Reboot Required'),
					'class' => 'spin oneemhigh'
				];
				$g4fl->
					addA(['class' => 'g4fi','href' => '/reboot.php'])->
						insIMG($img_attributes);
			endif;
		endif;
		$g4fx->addTDwC('g4fc',get_product_copyright());
		$g4fx->addTDwC('g4fr');
		return $this;
	}
}
class co_DOMElement extends \DOMElement implements ci_DOM {
	use co_DOMTools;

	public function addAttributes($attributes = []) {
		foreach($attributes as $key => $value):
			$this->setAttribute($key,$value);
		endforeach;
		return $this;
	}
	public function option_exists(string $option) {
		return $this->ownerDocument->option_exists($option);
	}
	public function push() {
		$this->ownerDocument->push($this);
		return $this;
	}
	public function pop() {
		return $this->ownerDocument->pop();
	}
	public function last() {
		return $this->ownerDocument->last();
	}
	public function reset_hooks() {
		$this->ownerDocument->reset_hooks();
		return $this;
	}
	public function add_hook($dom_element,string $identifier) {
		$this->ownerDocument->add_hook($dom_element,$identifier);
		return $this;
	}
	public function get_hooks() {
		return $this->ownerDocument->get_hooks();
	}
	public function add_js_on_load(string $jcode = '',string $key = NULL) {
		return $this->ownerDocument->add_js_on_load($jcode,$key);
	}
	public function add_js_document_ready(string $jcode = '',string $key = NULL) {
		return $this->ownerDocument->add_js_document_ready($jcode,$key);
	}
}
class co_DOMDocument extends \DOMDocument implements ci_DOM {
	use co_DOMTools;

	protected $hook_stack = [];
	protected $stack = [];
	protected $options = [];
	protected $js_on_load = [];
	protected $js_document_ready = [];

	public function __construct(string $version = '1.0',string $encoding = 'UTF-8') {
		parent::__construct($version,$encoding);
		$this->preserveWhiteSpace = false;
		$this->formatOutput = true;
		$this->registerNodeClass('DOMElement','co_DOMElement');
	}
	public function set_options(string ...$options) {
		foreach($options as $value):
			$this->options[$value] = $value;
		endforeach;
		return $this;
	}
	public function option_exists(string $option) {
		return array_key_exists($option,$this->options);
	}
	public function push($element) {
		array_push($this->stack,$element);
		return $element;
	}
	public function pop() {
		return array_pop($this->stack);
	}
	public function last() {
		return $this->stack[array_key_last($this->stack)];
	}
	public function reset_hooks() {
		$this->hook_stack = [];
	}
	public function add_hook($dom_element,string $identifier) {
		$this->hook_stack[$identifier] = $dom_element;
		return $this;
	}
	public function get_hooks() {
		return $this->hook_stack;
	}
	public function add_js_on_load(string $jcode = '',string $key = NULL) {
		if(preg_match('/\S/',$jcode)):
			if(isset($key)):
				$this->js_on_load[$key] = $jcode;
			else:
				$this->js_on_load[] = $jcode;
			endif;
		endif;
		return $this;
	}
	public function add_js_document_ready(string $jcode = '',string $key = NULL) {
		if(preg_match('/\S/',$jcode)):
			if(isset($key)):
				$this->js_document_ready[$key] = $jcode;
			else:
				$this->js_document_ready[] = $jcode;
			endif;
		endif;
		return $this;
	}
	protected function clc_javascript() {
		$body = $this->getElementById('main');
		if(isset($body)):
			if(!empty($this->js_on_load)):
				$jdata = implode(PHP_EOL,[
					'$(window).on("load", function() {',
					implode(PHP_EOL,$this->js_on_load),
					'});'
				]);
				$body->addJavaScript($jdata);
			endif;
			if(!empty($this->js_document_ready)):
				$jdata = implode(PHP_EOL,[
					'$(document).ready(function() {',
					implode(PHP_EOL,$this->js_document_ready),
					'});'
				]);
				$body->addJavaScript($jdata);
			endif;
		endif;
		return $this;
	}
	public function render() {
		$this->clc_javascript();
		echo $this->saveHTML();
		return $this;
	}
	public function get_html() {
		$this->clc_javascript();
		return $this->saveHTML();
	}
}
interface ci_DOM {
}
/**
 *
 * @param array $page_title
 * @param string $action_url
 * @param string ...$options
 * @return \co_DOMDocument
 */
/*
 *	login
 *	datechooser
 *	multipart
 *	nospinonsubmit
 *	notabnav
 *	tablesort
 *		tablesort-widgets
 *		sorter-bytestring
 *		sorter-checkbox
 *		sorter-radio
 */
function new_page(array $page_title = [],string $action_url = NULL,string ...$options) {
	$document = new co_DOMDocument();
	$document->
		loadHTML('<!DOCTYPE html>',LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	$document->
		set_options(...$options)->
		addElement('html',['lang' => system_get_language_code()])->
			ins_head($page_title)->
			ins_body($page_title,$action_url);
	return $document;
}
