<?php
/**
 * BitsMesh Theme Settings View
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) exit();

// Get active tab
$activeTab = $this->data('ActiveTab', 'checkin');
?>

<style>
.bits-settings-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}
.bits-settings-tabs a {
    padding: 12px 20px;
    color: #666;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}
.bits-settings-tabs a:hover {
    color: #333;
}
.bits-settings-tabs a.active {
    color: #22c55e;
    border-bottom-color: #22c55e;
}
.bits-tab-content {
    display: none;
}
.bits-tab-content.active {
    display: block;
}
.invite-codes-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}
.invite-codes-table th,
.invite-codes-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
.invite-codes-table th {
    background: #f5f5f5;
    font-weight: 600;
}
.invite-codes-table code {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
.status-active { color: #22c55e; }
.status-expired { color: #ef4444; }
.status-exhausted { color: #6b7280; }
.status-disabled { color: #9ca3af; }
.invite-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}
.invite-stat-item {
    background: #f5f5f5;
    padding: 15px 20px;
    border-radius: 8px;
    text-align: center;
}
.invite-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #22c55e;
}
.invite-stat-label {
    font-size: 0.85rem;
    color: #666;
}
.generated-codes-display {
    background: #f0fdf4;
    border: 1px solid #22c55e;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}
.generated-codes-display code {
    display: block;
    font-size: 1.1rem;
    margin: 5px 0;
}
</style>

<?php echo heading(t('BitsMesh 主题设置'), '', '', [], '/dashboard/settings/bitsmesh'); ?>

<div class="bits-settings-tabs">
    <a href="?tab=checkin" class="<?php echo $activeTab === 'checkin' ? 'active' : ''; ?>">签到设置</a>
    <a href="?tab=invite" class="<?php echo $activeTab === 'invite' ? 'active' : ''; ?>">邀请码管理</a>
    <a href="?tab=partners" class="<?php echo $activeTab === 'partners' ? 'active' : ''; ?>">合作商家</a>
    <a href="?tab=links" class="<?php echo $activeTab === 'links' ? 'active' : ''; ?>">友站链接</a>
</div>

<div class="padded">
    <!-- 签到设置 Tab -->
    <div class="bits-tab-content <?php echo $activeTab === 'checkin' ? 'active' : ''; ?>" id="tab-checkin">
        <?php echo $this->Form->open(['action' => url('/dashboard/settings/bitsmesh?tab=checkin')]); ?>
        <?php echo $this->Form->errors(); ?>

        <section>
            <h2 class="subheading"><?php echo t('签到奖励设置'); ?></h2>
            <div class="padded-top">
                <p class="info"><?php echo t('配置每日签到时鸡腿奖励的二项分布随机算法参数。'); ?></p>
                <p class="info"><?php echo t('二项分布 B(n, p) 的期望值 = n × p，表示用户平均每次签到获得的鸡腿数。'); ?></p>
            </div>

            <ul>
                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('最大鸡腿数 (n)', 'CheckIn_DistributionN'); ?>
                        <div class="info"><?php echo t('二项分布的试验次数，决定理论最大值。范围：10-200'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('CheckIn_DistributionN', [
                            'type' => 'number',
                            'min' => 10,
                            'max' => 200,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('成功概率 (p)', 'CheckIn_DistributionP'); ?>
                        <div class="info"><?php echo t('每次试验成功的概率，影响期望值。范围：0.01-0.5'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('CheckIn_DistributionP', [
                            'type' => 'number',
                            'min' => 0.01,
                            'max' => 0.5,
                            'step' => 0.01
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('保底最小值', 'CheckIn_MinAmount'); ?>
                        <div class="info"><?php echo t('无论运气多差，签到至少获得的鸡腿数。范围：1-10'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('CheckIn_MinAmount', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => 10,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>
            </ul>

            <?php
            // 计算并显示分布预览
            $n = $this->data('CheckIn_DistributionN', 50);
            $p = $this->data('CheckIn_DistributionP', 0.1);
            $min = $this->data('CheckIn_MinAmount', 1);
            $expectedValue = round($n * $p, 1);

            // 计算标准差
            $stdDev = round(sqrt($n * $p * (1 - $p)), 1);

            // 约 95% 的用户落在 [E-2σ, E+2σ] 范围内
            $range95Low = max($min, round($expectedValue - 2 * $stdDev));
            $range95High = round($expectedValue + 2 * $stdDev);
            ?>
            <div class="padded alert alert-info">
                <strong><?php echo t('📊 分布预览'); ?></strong>
                <ul style="margin: 10px 0 0 20px; list-style: disc;">
                    <li><?php echo sprintf(t('期望值（平均）：约 %s 鸡腿'), '<strong>' . $expectedValue . '</strong>'); ?></li>
                    <li><?php echo sprintf(t('约 95%% 的用户获得：%d ~ %d 鸡腿'), $range95Low, $range95High); ?></li>
                    <li><?php echo sprintf(t('理论最大值：%d 鸡腿（概率极低）'), $n); ?></li>
                    <li><?php echo sprintf(t('保底最小值：%d 鸡腿'), $min); ?></li>
                </ul>
            </div>
        </section>

        <?php echo $this->Form->close('保存设置'); ?>
    </div>

    <!-- 邀请码管理 Tab -->
    <div class="bits-tab-content <?php echo $activeTab === 'invite' ? 'active' : ''; ?>" id="tab-invite">
        <?php
        $inviteStats = $this->data('InviteStats', []);
        $inviteCodes = $this->data('InviteCodes', []);
        $generatedCodes = $this->data('GeneratedCodes', []);
        ?>

        <!-- 统计数据 -->
        <section>
            <h2 class="subheading"><?php echo t('邀请码统计'); ?></h2>
            <div class="invite-stats">
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo $inviteStats['total'] ?? 0; ?></div>
                    <div class="invite-stat-label">总邀请码</div>
                </div>
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo $inviteStats['active'] ?? 0; ?></div>
                    <div class="invite-stat-label">可用</div>
                </div>
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo $inviteStats['exhausted'] ?? 0; ?></div>
                    <div class="invite-stat-label">已用完</div>
                </div>
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo $inviteStats['expired'] ?? 0; ?></div>
                    <div class="invite-stat-label">已过期</div>
                </div>
                <div class="invite-stat-item">
                    <div class="invite-stat-value"><?php echo $inviteStats['totalInvited'] ?? 0; ?></div>
                    <div class="invite-stat-label">邀请注册用户</div>
                </div>
            </div>
        </section>

        <!-- 用户兑换配置 -->
        <section>
            <h2 class="subheading"><?php echo t('用户兑换配置'); ?></h2>
            <?php echo $this->Form->open(['action' => url('/dashboard/settings/bitsmesh?tab=invite&action=saveconfig')]); ?>
            <?php echo $this->Form->errors(); ?>

            <ul>
                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('用户兑换成本（鸡腿）', 'Invite_CreditCost'); ?>
                        <div class="info"><?php echo t('用户生成一个邀请码需要消耗的鸡腿数量'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Invite_CreditCost', [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 100000,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('默认可用次数', 'Invite_DefaultMaxUses'); ?>
                        <div class="info"><?php echo t('用户生成的邀请码默认可使用次数'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Invite_DefaultMaxUses', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => 100,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('默认有效期（天）', 'Invite_DefaultExpiryDays'); ?>
                        <div class="info"><?php echo t('用户生成的邀请码默认有效天数'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Invite_DefaultExpiryDays', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => 365,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('邀请成功奖励（鸡腿）', 'Invite_InviterBonus'); ?>
                        <div class="info"><?php echo t('被邀请用户注册成功后，邀请人获得的鸡腿奖励'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Invite_InviterBonus', [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 10000,
                            'step' => 1
                        ]); ?>
                    </div>
                </li>
            </ul>

            <?php echo $this->Form->close('保存配置'); ?>
        </section>

        <!-- 管理员生成邀请码 -->
        <section>
            <h2 class="subheading"><?php echo t('生成邀请码'); ?></h2>
            <?php echo $this->Form->open(['action' => url('/dashboard/settings/bitsmesh?tab=invite&action=generate')]); ?>

            <ul>
                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('生成数量', 'Admin_CodeCount'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Admin_CodeCount', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => 100,
                            'step' => 1,
                            'value' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('每个邀请码可用次数', 'Admin_MaxUses'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Admin_MaxUses', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => 1000,
                            'step' => 1,
                            'value' => 1
                        ]); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('有效期（天，0 = 永不过期）', 'Admin_ExpiryDays'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Admin_ExpiryDays', [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 365,
                            'step' => 1,
                            'value' => 30
                        ]); ?>
                    </div>
                </li>
            </ul>

            <?php echo $this->Form->close('生成邀请码'); ?>

            <?php if (!empty($generatedCodes)): ?>
            <div class="generated-codes-display">
                <strong>✅ 生成成功！新邀请码：</strong>
                <?php foreach ($generatedCodes as $code): ?>
                <code><?php echo htmlspecialchars($code); ?></code>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- 邀请码列表 -->
        <section>
            <h2 class="subheading"><?php echo t('邀请码列表'); ?></h2>

            <?php if (empty($inviteCodes)): ?>
            <p class="info"><?php echo t('暂无邀请码'); ?></p>
            <?php else: ?>
            <table class="invite-codes-table">
                <thead>
                    <tr>
                        <th>邀请码</th>
                        <th>创建者</th>
                        <th>使用情况</th>
                        <th>过期时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inviteCodes as $code):
                        $isExpired = $code['ExpiresAt'] && strtotime($code['ExpiresAt']) < time();
                        $isExhausted = $code['UseCount'] >= $code['MaxUses'];
                        $isActive = $code['IsActive'] && !$isExpired && !$isExhausted;

                        if (!$code['IsActive']) {
                            $status = 'disabled';
                            $statusText = '已禁用';
                        } elseif ($isExpired) {
                            $status = 'expired';
                            $statusText = '已过期';
                        } elseif ($isExhausted) {
                            $status = 'exhausted';
                            $statusText = '已用完';
                        } else {
                            $status = 'active';
                            $statusText = '可用';
                        }
                    ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($code['Code']); ?></code></td>
                        <td><?php echo $code['CreatorUserID'] == 0 ? '管理员' : htmlspecialchars($code['CreatorName'] ?? 'User #' . $code['CreatorUserID']); ?></td>
                        <td><?php echo $code['UseCount']; ?>/<?php echo $code['MaxUses']; ?></td>
                        <td><?php echo $code['ExpiresAt'] ? date('Y-m-d', strtotime($code['ExpiresAt'])) : '永不过期'; ?></td>
                        <td><span class="status-<?php echo $status; ?>"><?php echo $statusText; ?></span></td>
                        <td>
                            <a href="<?php echo url('/dashboard/settings/bitsmesh?tab=invite&action=toggle&id=' . $code['InviteCodeID'] . '&tk=' . Gdn::session()->transientKey()); ?>">
                                <?php echo $code['IsActive'] ? '禁用' : '启用'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </div>

    <!-- 合作商家 Tab -->
    <div class="bits-tab-content <?php echo $activeTab === 'partners' ? 'active' : ''; ?>" id="tab-partners">
        <?php
        $partners = $this->data('Partners', []);
        $editPartnerIndex = $this->data('EditPartnerIndex', -1);
        $editPartner = $editPartnerIndex >= 0 && isset($partners[$editPartnerIndex]) ? $partners[$editPartnerIndex] : null;
        ?>

        <!-- 已有商家列表 -->
        <section>
            <h2 class="subheading"><?php echo t('商家列表'); ?></h2>

            <?php if (empty($partners)): ?>
            <p class="info"><?php echo t('暂无合作商家，请添加'); ?></p>
            <?php else: ?>
            <table class="invite-codes-table">
                <thead>
                    <tr>
                        <th style="width:60px">Logo</th>
                        <th>名称</th>
                        <th>描述</th>
                        <th>链接</th>
                        <th style="width:120px">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $index => $partner): ?>
                    <tr>
                        <td>
                            <?php if (!empty($partner['Logo'])): ?>
                            <img src="<?php echo htmlspecialchars($partner['Logo']); ?>" width="40" height="40" style="object-fit:contain;border-radius:4px;" />
                            <?php else: ?>
                            <span style="color:#999;">无</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($partner['Name']); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($partner['Description'] ?? '', 0, 30)); ?><?php echo mb_strlen($partner['Description'] ?? '') > 30 ? '...' : ''; ?></td>
                        <td><a href="<?php echo htmlspecialchars($partner['Url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(mb_substr($partner['Url'], 0, 30)); ?></a></td>
                        <td>
                            <a href="?tab=partners&action=edit&index=<?php echo $index; ?>"><?php echo t('编辑'); ?></a>
                            &nbsp;|&nbsp;
                            <a href="?tab=partners&action=delete&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>" onclick="return confirm('确定删除？');"><?php echo t('删除'); ?></a>
                            <?php if ($index > 0): ?>
                            &nbsp;|&nbsp;
                            <a href="?tab=partners&action=moveup&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>">↑</a>
                            <?php endif; ?>
                            <?php if ($index < count($partners) - 1): ?>
                            &nbsp;|&nbsp;
                            <a href="?tab=partners&action=movedown&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>">↓</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>

        <!-- 添加/编辑表单 -->
        <section>
            <h2 class="subheading"><?php echo $editPartner ? t('编辑商家') : t('添加商家'); ?></h2>
            <?php echo $this->Form->open(['action' => url('/dashboard/settings/bitsmesh?tab=partners&action=save' . ($editPartnerIndex >= 0 ? '&index=' . $editPartnerIndex : ''))]); ?>
            <?php echo $this->Form->errors(); ?>

            <ul>
                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('名称 *', 'Partner_Name'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Partner_Name', ['value' => $editPartner['Name'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('Logo URL', 'Partner_Logo'); ?>
                        <div class="info"><?php echo t('留空使用默认图标'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Partner_Logo', ['value' => $editPartner['Logo'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('描述', 'Partner_Description'); ?>
                        <div class="info"><?php echo t('简短描述，最多 200 字'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Partner_Description', ['MultiLine' => true, 'value' => $editPartner['Description'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('链接地址 *', 'Partner_Url'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Partner_Url', ['value' => $editPartner['Url'] ?? '']); ?>
                    </div>
                </li>
            </ul>

            <?php echo $this->Form->close($editPartner ? '保存修改' : '添加商家'); ?>
            <?php if ($editPartner): ?>
            <p><a href="?tab=partners"><?php echo t('取消编辑'); ?></a></p>
            <?php endif; ?>
        </section>
    </div>

    <!-- 友站链接 Tab -->
    <div class="bits-tab-content <?php echo $activeTab === 'links' ? 'active' : ''; ?>" id="tab-links">
        <?php
        $friendLinks = $this->data('FriendLinks', []);
        $editLinkIndex = $this->data('EditLinkIndex', -1);
        $editLink = $editLinkIndex >= 0 && isset($friendLinks[$editLinkIndex]) ? $friendLinks[$editLinkIndex] : null;
        ?>

        <!-- 已有链接列表 -->
        <section>
            <h2 class="subheading"><?php echo t('链接列表'); ?></h2>

            <?php if (empty($friendLinks)): ?>
            <p class="info"><?php echo t('暂无友站链接，请添加'); ?></p>
            <?php else: ?>
            <table class="invite-codes-table">
                <thead>
                    <tr>
                        <th style="width:60px">Logo</th>
                        <th>名称</th>
                        <th>描述</th>
                        <th>链接</th>
                        <th style="width:120px">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($friendLinks as $index => $link): ?>
                    <tr>
                        <td>
                            <?php if (!empty($link['Logo'])): ?>
                            <img src="<?php echo htmlspecialchars($link['Logo']); ?>" width="40" height="40" style="object-fit:contain;border-radius:4px;" />
                            <?php else: ?>
                            <span style="color:#999;">无</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($link['Name']); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($link['Description'] ?? '', 0, 30)); ?><?php echo mb_strlen($link['Description'] ?? '') > 30 ? '...' : ''; ?></td>
                        <td><a href="<?php echo htmlspecialchars($link['Url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(mb_substr($link['Url'], 0, 30)); ?></a></td>
                        <td>
                            <a href="?tab=links&action=edit&index=<?php echo $index; ?>"><?php echo t('编辑'); ?></a>
                            &nbsp;|&nbsp;
                            <a href="?tab=links&action=delete&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>" onclick="return confirm('确定删除？');"><?php echo t('删除'); ?></a>
                            <?php if ($index > 0): ?>
                            &nbsp;|&nbsp;
                            <a href="?tab=links&action=moveup&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>">↑</a>
                            <?php endif; ?>
                            <?php if ($index < count($friendLinks) - 1): ?>
                            &nbsp;|&nbsp;
                            <a href="?tab=links&action=movedown&index=<?php echo $index; ?>&tk=<?php echo Gdn::session()->transientKey(); ?>">↓</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>

        <!-- 添加/编辑表单 -->
        <section>
            <h2 class="subheading"><?php echo $editLink ? t('编辑链接') : t('添加链接'); ?></h2>
            <?php echo $this->Form->open(['action' => url('/dashboard/settings/bitsmesh?tab=links&action=save' . ($editLinkIndex >= 0 ? '&index=' . $editLinkIndex : ''))]); ?>
            <?php echo $this->Form->errors(); ?>

            <ul>
                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('名称 *', 'Link_Name'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Link_Name', ['value' => $editLink['Name'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('Logo URL', 'Link_Logo'); ?>
                        <div class="info"><?php echo t('留空使用默认图标'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Link_Logo', ['value' => $editLink['Logo'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('描述', 'Link_Description'); ?>
                        <div class="info"><?php echo t('简短描述，最多 200 字'); ?></div>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Link_Description', ['MultiLine' => true, 'value' => $editLink['Description'] ?? '']); ?>
                    </div>
                </li>

                <li class="form-group">
                    <div class="label-wrap">
                        <?php echo $this->Form->label('链接地址 *', 'Link_Url'); ?>
                    </div>
                    <div class="input-wrap">
                        <?php echo $this->Form->textBox('Link_Url', ['value' => $editLink['Url'] ?? '']); ?>
                    </div>
                </li>
            </ul>

            <?php echo $this->Form->close($editLink ? '保存修改' : '添加链接'); ?>
            <?php if ($editLink): ?>
            <p><a href="?tab=links"><?php echo t('取消编辑'); ?></a></p>
            <?php endif; ?>
        </section>
    </div>
</div>
