<style>
<!--
.menu_link_list {
    list-style-type:none;
    list-style-position:outside;
    margin-left:20px;
    padding:10px 0;
}

.menu_link_element {
    margin-top:10px;
	background:#e5e5e5;
	border:1px solid #8F8F8F;
	padding:5px
}
.menu_link_menu {
    margin-top:15px;
	background:#EFEFEF;
	border:1px solid #8F8F8F;
}

.menu_link_element:hover {
    border-color:#aaaaaa;
    cursor:move;
}

.menu_link_element label { color:#1F507F; }
.menu_link_element:hover label { cursor:move; }
.menu_link_element:hover img { cursor:pointer; }
.menu_link_element:hover label { cursor:pointer; }

/* Interface des menus */
#valid_position_menus {
	color:#0E2A48;
	position:fixed;
	z-index:101;
	top: 90%;
	right:0px;
	margin-right:5px;
	padding:6px;
	border:1px solid #353535;
	background:#366393;
	border-radius:5px;
}

.menu_block_libelle {
	color:#0E2A48;
	width:165px;
	height:20px;
	margin:0px;
	text-align:center;
	font-weight:bold;
	background:#f9f9f9;
	border:1px solid #8F8F8F;
	border-bottom:none;
	-moz-border-radius:4px 4px 0px 0px;
	-khtml-border-radius-topleft:5px;
	-khtml-border-radius-topright:5px;
	-webkit-border-top-left-radius:5px;
	-webkit-border-top-right-radius:5px;
}
.menus_block_add {
	color:#0E2A48;
	width:165px;
	height:20px;
	margin:0px;
	background:#f9f9f9;
	border:1px solid #8F8F8F;
	border-top:none;
	cursor:pointer;
	text-align:center;
	-moz-border-radius:0px 0px 4px 4px;
	-khtml-border-radius-bottomleft:5px;
	-khtml-border-radius-bottomright:5px;
	-webkit-border-bottom-left-radius:5px;
	-webkit-border-bottom-right-radius:5px;
}
.menus_block_add_links {
	padding:2px 0px;
	-moz-border-radius:0px;
	-khtml-border-radius-bottomleft:0px;
	-khtml-border-radius-bottomright:0px;
	-webkit-border-bottom-left-radius:0px;
	-webkit-border-bottom-right-radius:0px;
}
.menus_block_container {
	width:190px;
	margin:0px;
	margin-bottom:6px;
	padding:5px;
	padding-bottom:7px;
	background:#d1d1d1;
	border:1px solid #8F8F8F;
	cursor:move;
   	-moz-border-radius:3px;
	-khtml-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
	overflow:auto;
}
.menus_block_top { margin-bottom:5px; }
.menus_block_title { color:#515C68; }

.menus_block_move {
	float:right;
	background:none;
	height:16px;
	width:16px;
}

.menus_block_move a{
	display:block;
	height:16px;
	width:16px;	
}

.menu_spacer {
	width:99%;
	padding:0px 5px;
	margin:0;
	height:15px;
}
-->
</style>

<script type="text/javascript">
<!--
Event.observe(window, 'load', function() {
	Sortable.create('menu_element_list', {tree:true,dropOnEmpty: true,scroll:window,format: /^menu_element_([0-9]+)$/});   
});
-->
</script>

<ul id="menu_element_lists" class="menu_link_list" style="position: relative;">
	<li class="menu_link_element menu_link_menu" style="position: relative;">
		<ul id="menu_element_list" class="menu_link_list" style="position: relative;">
			# START childrens #
				{childrens.child}
			# END childrens #
		</ul>
	</li>
</ul>