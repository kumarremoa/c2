<?php

function cat_list_one_new($cat_id = 0, $cat_level = 0, $sel_cat)
{
	if ($cat_id == 0) {
		$arr = cat_list($cat_id);
		return $arr;
	}
	else {
		$arr = cat_list($cat_id);

		foreach ($arr as $key => $value) {
			if ($key == $cat_id) {
				unset($arr[$cat_id]);
			}
		}

		$str = '';

		if ($arr) {
			$cat_level++;

			switch ($sel_cat) {
			case 'sel_cat_edit':
				$str .= '<select name=\'catList' . $cat_level . '\' id=\'cat_list' . $cat_level . '\' onchange=\'getGoods(this.value, ' . $cat_level . ')\' class=\'select\'>';
				break;

			case 'sel_cat_picture':
				$str .= '<select name=\'catList' . $cat_level . '\' id=\'cat_list' . $cat_level . '\' onchange=\'goods_list(this, ' . $cat_level . ')\' class=\'select\'>';
				break;

			case 'sel_cat_goodslist':
				$str .= '<select class=\'select mr10\' name=\'movecatList' . $cat_level . '\' id=\'move_cat_list' . $cat_level . '\' onchange=\'movecatList(this.value, ' . $cat_level . ')\'>';
				break;

			default:
				break;
			}

			$str .= '<option value=\'0\'>全部分类</option>';

			foreach ($arr as $key1 => $value1) {
				$str .= '<option value=\'' . $value1['cat_id'] . '\'>' . $value1['cat_name'] . '</option>';
			}

			$str .= '</select>';
		}

		return $str;
	}
}

function add_link($extension_code = '')
{
	$href = 'goods.php?act=add';

	if (!empty($extension_code)) {
		$href .= '&extension_code=' . $extension_code;
	}

	if ($extension_code == 'virtual_card') {
		$text = $GLOBALS['_LANG']['51_virtual_card_add'];
	}
	else {
		$text = $GLOBALS['_LANG']['02_goods_add'];
	}

	return array('href' => $href, 'text' => $text, 'class' => 'icon-plus');
}

function get_order_no_comment_goods($ru_id = 0, $sign = 0)
{
	$where = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . '  AND oi.shipping_status = \'' . SS_RECEIVED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED));
	$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = og.order_id) = 0 ';

	if ($sign == 0) {
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.ru_id = \'' . $ru_id . '\') = 0 ');
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . 'LEFT JOIN  ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . ('WHERE og.ru_id = \'' . $ru_id . '\' ' . $where . ' ');
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT og.*, oi.*,g.goods_thumb, u.user_name FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . 'LEFT JOIN  ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . 'LEFT JOIN  ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = oi.user_id ' . ('WHERE og.ru_id = \'' . $ru_id . '\' ' . $where . ' ') . ' ORDER BY oi.order_id DESC ' . ' LIMIT ' . $filter['start'] . (',' . $filter['page_size']);
	$arr = $GLOBALS['db']->getAll($sql);
	return $arr;
}

function list_link($is_add = true, $extension_code = '')
{
	$href = 'goods.php?act=list';

	if (!empty($extension_code)) {
		$href .= '&extension_code=' . $extension_code;
	}

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	if ($extension_code == 'virtual_card') {
		$text = $GLOBALS['_LANG']['50_virtual_card_list'];
	}
	else {
		$text = $GLOBALS['_LANG']['01_goods_list'];
	}

	return array('href' => $href, 'text' => $text);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/' . SELLER_PATH . '/includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');
$exc_extend = new exchange($ecs->table('goods_extend'), $db, 'goods_id', 'extend_id');
$exc_gallery = new exchange($ecs->table('goods_gallery'), $db, 'img_id', 'goods_id');
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'goods');
$admin_id = get_admin_id();
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$ru_id = $adminru['ru_id'];
$smarty->assign('review_goods', $GLOBALS['_CFG']['review_goods']);
$commission_setting = admin_priv('commission_setting', '', false);
$smarty->assign('commission_setting', $commission_setting);
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'manage') {
	admin_priv('goods_manage');
	$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('products_changelog') . 'WHERE admin_id = \'' . $_SESSION['seller_id'] . '\'';
	$GLOBALS['db']->query($sql);
	get_del_goodsimg_null();
	get_del_goods_gallery();
	get_updel_goods_attr();
	$smarty->assign('primary_cat', $_LANG['02_cat_and_goods']);


	//$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '02_proxy_goods'));
	if ($_REQUEST['act'] == 'list') {
		$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '02_proxy_goods'));
		$tab_menu = array();
		$tab_menu[] = array('curr' => 1, 'text' => $_LANG['02_proxy_goods'], 'href' => 'proxy_goods.php?act=list');
		$tab_menu[] = array('curr' => 0, 'text' => "管理代理商品", 'href' => 'proxy_goods.php?act=manage');
		$smarty->assign('tab_menu', $tab_menu);
	}
	if ($_REQUEST['act'] == 'manage') {
			$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '02_proxy_goods'));
			$tab_menu = array();
			$tab_menu[] = array('curr' => 0, 'text' => $_LANG['02_proxy_goods'], 'href' => 'proxy_goods.php?act=list');
			$tab_menu[] = array('curr' => 1, 'text' => "管理代理商品", 'href' => 'proxy_goods.php?act=manage');
			$smarty->assign('tab_menu', $tab_menu);
		}	
	


	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	$suppliers_id = isset($_REQUEST['suppliers_id']) ? (empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id'])) : '';
	$is_on_sale = isset($_REQUEST['is_on_sale']) ? (empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0 ? '' : trim($_REQUEST['is_on_sale'])) : '';
	$handler_list = array();
	$handler_list['virtual_card'][] = array('url' => 'virtual_card.php?act=card', 'title' => $_LANG['card'], 'icon' => 'icon-credit-card');
	$handler_list['virtual_card'][] = array('url' => 'virtual_card.php?act=replenish', 'title' => $_LANG['replenish'], 'icon' => 'icon-plus-sign');
	$handler_list['virtual_card'][] = array('url' => 'virtual_card.php?act=batch_card_add', 'title' => $_LANG['batch_card_add'], 'icon' => 'icon-plus-sign');

	$suppliers_list_name = suppliers_list_name();
	$suppliers_exists = 1;

	if (empty($suppliers_list_name)) {
		$suppliers_exists = 0;
	}

	$smarty->assign('is_on_sale', $is_on_sale);
	$smarty->assign('suppliers_id', $suppliers_id);
	$smarty->assign('suppliers_exists', $suppliers_exists);
	$smarty->assign('suppliers_list_name', $suppliers_list_name);
	unset($suppliers_list_name);
	unset($suppliers_exists);
	$ur_here = $_REQUEST['act'] == 'list' ? $_LANG['02_proxy_goods'] : "管理代理商品";
	$smarty->assign('ur_here', $ur_here);

	$smarty->assign('code', $code);
	$smarty->assign('brand_list', get_brand_list());
	$smarty->assign('intro_list', get_intro_list());
	$smarty->assign('lang', $_LANG);
	$smarty->assign('list_type', $_REQUEST['act'] == 'list' ? 'goods' : 'trash');
	$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
	$suppliers_list = suppliers_list_info(' is_check = 1 ');
	$suppliers_list_count = count($suppliers_list);
	$smarty->assign('suppliers_list', $suppliers_list_count == 0 ? 0 : $suppliers_list);
	$goods_list = get_proxy_goods_list($_REQUEST['act'] == 'list' ? 0 : 1);

	$smarty->assign('goods_list', $goods_list['goods']);
	$smarty->assign('filter', $goods_list['filter']);
	$smarty->assign('record_count', $goods_list['record_count']);
	$smarty->assign('page_count', $goods_list['page_count']);
	$smarty->assign('full_page', 1);
	$no_com = get_order_no_comment_goods($ru_id, 0);
	$smarty->assign('no_com_goods', $no_com);
	$page_count_arr = seller_page($goods_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($goods_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$specifications = get_goods_type_specifications();
	$smarty->assign('specifications', $specifications);
	$smarty->assign('nowTime', gmtime());
	$smarty->assign('user_id', $adminru['ru_id']);
	set_default_filter(0, 0, $adminru['ru_id']);
	$smarty->assign('transport_list', get_table_date('goods_transport', 'ru_id=\'' . $adminru['ru_id'] . '\'', array('tid, title'), 1));
	assign_query_info();
	$htm_file = $_REQUEST['act'] == 'list' ? 'proxy_goods_list.dwt' : 'proxy_goods_manage.dwt';
	$smarty->display($htm_file);
}
else if ($_REQUEST['act'] == 'query') {
	$is_delete = empty($_REQUEST['is_delete']) ? 0 : intval($_REQUEST['is_delete']);
	$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	$goods_list = get_proxy_goods_list(3);


	$smarty->assign('code', $code);
	$smarty->assign('goods_list', $goods_list['goods']);
	$smarty->assign('filter', $goods_list['filter']);
	$smarty->assign('record_count', $goods_list['record_count']);
	$smarty->assign('page_count', $goods_list['page_count']);
	$smarty->assign('list_type', $is_delete ? 'trash' : 'goods');
	$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
	$page_count_arr = seller_page($goods_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$sort_flag = sort_flag($goods_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$specifications = get_goods_type_specifications();
	$smarty->assign('specifications', $specifications);
	$tpl = $_REQUEST['is_proxy'] == 0 ? 'proxy_goods_list.dwt' : 'proxy_goods_manage.dwt';
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$smarty->assign('transport_list', get_table_date('goods_transport', 'ru_id=\'' . $adminru['ru_id'] . '\'', array('tid, title'), 1));
	$smarty->assign('nowTime', gmtime());
	make_json_result($smarty->fetch($tpl), '', array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']));
}
else if ($_REQUEST['act'] == 'to_change_proxy'){
	$goods_id = intval($_POST['id']);
	$val = intval($_POST['val']);

	$res = change_proxy($ru_id,$goods_id,$val);
	$rs = array();
	if($res == 1){
		$rs['message'] = "代理成功";
	}
	clear_cache_files();
	make_json_result($rs);
}
else if ($_REQUEST['act'] == 'batch') {
	$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	$goods_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'to_proxy') {
			$goods_id_arr = explode(',',$goods_id);
			foreach($goods_id_arr as $k=>$v){
				if($v != 'on'){
					change_proxy($ru_id,$v,1);
				}
			}
			clear_cache_files();
			$link[0] = array('text' => '返回列表', 'href' => 'proxy_goods.php?act=list');
			sys_msg("批量加入销售成功", 0, $link);
		}
		if ($_POST['type'] == 'not_to_proxy') {
			$goods_id_arr = explode(',',$goods_id);
			foreach($goods_id_arr as $k=>$v){
				if($v != 'on'){
					change_proxy($ru_id,$v,0);
				}
			}
			clear_cache_files();
			$link[0] = array('text' => '返回列表', 'href' => 'proxy_goods.php?act=manage');
			sys_msg("批量下架销售成功", 0, $link);
		}
	}
	$link[0] = array('text' => '返回列表', 'href' => 'proxy_goods.php?act=list');
	sys_msg("操作失败", 0, $link);
}
else {
	
	
}

?>
