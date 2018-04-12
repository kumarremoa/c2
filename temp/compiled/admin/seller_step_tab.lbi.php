<div class="tabs_info">
    <ul>
        <li <?php if ($this->_var['menu_select']['current'] == '01_seller_stepup'): ?>class="curr"<?php endif; ?>>
            <a href="merchants_steps.php?act=step_up">店铺设置</a>
        </li>
        <li <?php if ($this->_var['menu_select']['current'] == '01_merchants_steps_list'): ?>class="curr"<?php endif; ?>>
            <a href="merchants_steps.php?act=list">入驻流程</a>
        </li>
        <li <?php if ($this->_var['menu_select']['current'] == '03_users_merchants_priv'): ?>class="curr"<?php endif; ?>>
            <a href="merchants_privilege.php?act=allot">入驻初始化权限</a>
        </li>
        <li <?php if ($this->_var['menu_select']['current'] == '10_seller_grade'): ?>class="curr"<?php endif; ?>>
            <a href="seller_grade.php?act=list">店铺等级</a>
        </li>
        <li <?php if ($this->_var['menu_select']['current'] == 'template_mall'): ?>class="curr"<?php endif; ?>>
            <a href="template_mall.php?act=list"><?php echo $this->_var['lang']['template_mall']; ?></a>
        </li>
        <li <?php if ($this->_var['menu_select']['current'] == 'template_apply_list'): ?>class="curr"<?php endif; ?>>
            <a href="template_mall.php?act=template_apply_list">模板订单</a>
        </li>
    </ul>
</div>	