\
\ Part of XigmaNAS (https://www.xigmanas.com).
\ XigmaNAS Copyright (c) 2018-2019 XigmaNAS(R) (info@xigmanas.com).
\ All Rights Reserved.
\
\ XigmaNAS(R) is a registered trademark of Michael Zoon. (zoon01@xigmanas.com).
\ All Rights Reserved.
\

2 brandX ! 1 brandY ! \ Initialize brand placement defaults

: brand+ ( x y c-addr/u -- x y' )
	2swap 2dup at-xy 2swap \ position the cursor
	type \ print to the screen
	1+ \ increase y for next time we're called
;

: brand ( x y -- ) \ "XigmaNAS" [wide] logo in B/W (6 rows x 50 columns)

	s" __  ___                       _   _    _    ____  " brand+
	s" \ \/ (_) __ _ _ __ ___   __ _| \ | |  / \  / ___| " brand+
	s"  \  /| |/ _` | '_ ` _ \ / _` |  \| | / _ \ \___ \ " brand+
	s"  /  \| | (_| | | | | | | (_| | |\  |/ ___ \ ___) |" brand+
	s" /_/\_\_|\__, |_| |_| |_|\__,_|_| \_/_/   \_\____/ " brand+
	s"         |___/                                     " brand+

	2drop
;
