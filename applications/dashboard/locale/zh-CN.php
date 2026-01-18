<?php if (!defined('APPLICATION')) exit();
/**
 * Dashboard 简体中文翻译
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @locale zh-CN
 * @translator BitsMesh
 */

if (!function_exists('FormatPossessive')) {
    /**
     * 中文版本的所有格（直接返回原词，中文无所有格变化）
     *
     * @param string $word 要格式化的词
     * @return string
     */
    function formatPossessive($word) {
        return $word . '的';
    }
}

$Definition['Apply for Membership'] = '注册';

$Definition['BanReason.1'] = '被管理员封禁。';
$Definition['BanReason.2'] = '因 IP 地址、邮箱或用户名被封禁。';
$Definition['BanReason.4'] = '被管理员临时封禁。';
$Definition['BanReason.8'] = '因警告次数过多被封禁。';

// 验证相关
$Definition['ValidateRegex'] = '%s 的格式不正确。';
$Definition['ValidateRequired'] = '%s 是必填项。';
$Definition['ValidateRequiredArray'] = '您必须至少选择一个 %s。';
$Definition['ValidateEmail'] = '%s 不是有效的邮箱地址。';
$Definition['ValidateFormat'] = '不允许发布原始 HTML 内容。';
$Definition['ValidateDate'] = '%s 不是有效的日期。';
$Definition['ValidateInteger'] = '%s 不是有效的整数。';
$Definition['ValidateBoolean'] = '%s 不是有效的布尔值。';
$Definition['ValidateDecimal'] = '%s 不是有效的小数。';
$Definition['ValidateTime'] = '%s 不是有效的时间。';
$Definition['ValidateTimestamp'] = '%s 不是有效的时间戳。';
$Definition['ValidateUsername'] = '用户名必须是 3-20 个字符，只能包含字母、数字和下划线。';
$Definition['ValidateLength'] = '%1$s 超出了 %2$s 个字符。';
$Definition['ValidateMinLength'] = '%1$s 少了 %2$s 个字符。';
$Definition['ValidateMinLengthSingular'] = '%1$s 少了 %2$s 个字符。';
$Definition['ValidateMinLengthPlural'] = '%1$s 少了 %2$s 个字符。';
$Definition['ValidateEnum'] = '%s 不是有效的选项。';
$Definition['ValidateOneOrMoreArrayItemRequired'] = '您必须至少选择一个 %s。';
$Definition['ValidateConnection'] = '您提供的连接参数无法连接到数据库。数据库返回错误：<code>%s</code>';
$Definition['ValidateMatch'] = '%s 两次输入不一致。';
$Definition['ValidateStrength'] = '您提供的 %s 强度太弱。请尝试使用更强的密码，并参考强度指示器。';
$Definition['ValidateVersion'] = '%s 不是有效的版本号。请参考 PHP 的 version_compare() 函数了解有效的版本号格式。';
$Definition['ValidateBanned'] = '该 %s 不被允许使用。';
$Definition['ValidateString'] = '%s 不是有效的字符串。';
$Definition['ValidateUrlStringRelaxed'] = '%s 不能包含斜杠、引号或标签字符。';
$Definition['ValidateUrl'] = '%s 不是有效的网址。';

// 错误信息
$Definition['ErrorPermission'] = '抱歉，您没有权限执行此操作。';
$Definition['InviteErrorPermission'] = '抱歉，您没有权限执行此操作。';
$Definition['PermissionRequired.Garden.Moderation.Manage'] = '您需要版主权限才能执行此操作。';
$Definition['PermissionRequired.Garden.Settings.Manage'] = '您需要管理员权限才能执行此操作。';
$Definition['PermissionRequired.Javascript'] = '您需要启用 JavaScript 才能执行此操作。';
$Definition['ErrorBadInvitationCode'] = '您提供的邀请码无效。';
$Definition['ErrorCredentials'] = '抱歉，未找到与您输入的邮箱/用户名和密码相关的账户。';
$Definition['User not found.'] = '抱歉，未找到与您输入的 %s 相关的账户。';
$Definition['Invalid password.'] = '您输入的密码不正确。请注意密码区分大小写。';
$Definition['ErrorRecordNotFound'] = '未找到请求的记录。';

// 分页
$Definition['PageDetailsMessageFull'] = '第 %1$s 至 %2$s 条，共 %3$s 条';
$Definition['PageDetailsMessage'] = '第 %1$s 至 %2$s 条';

// 字段名称
$Definition['RoleID'] = '角色';
$Definition['Garden.Registration.DefaultRoles'] = '默认角色';
$Definition['Garden.Title'] = '站点标题';
$Definition['Garden.Email.SupportName'] = '客服名称';
$Definition['Garden.Email.SupportAddress'] = '客服邮箱';
$Definition['UrlCode'] = '网址代码';
$Definition['OldPassword'] = '旧密码';

// 邮件模板
$Definition['EmailHeader'] = '{User.Name}，您好！
';
$Definition['EmailFooter'] = '
祝您使用愉快！';

$Definition['EmailInvitation'] = '您好！

%1$s 邀请您加入 %2$s。如果您想加入，请点击以下链接：

  %3$s';

$Definition['EmailMembershipApproved'] = '%1$s，您好！

您的会员资格已通过审核。请通过以下链接登录：

  %2$s';

$Definition['EmailPassword'] = '%2$s 已在 %3$s 为您的账户（邮箱：%6$s）重置了密码。如果您不知道新密码，请联系他们。

  网址：%4$s';

$Definition['EmailConfirmEmail'] = '您需要先验证邮箱地址才能继续。请点击以下链接验证您的邮箱：{/entry/emailconfirm,exurl,domain}/{User.UserID,rawurlencode}/{EmailKey,rawurlencode}';

$Definition['PasswordRequest'] = '有人请求在 %2$s 重置您的密码。要重置密码，请点击以下链接：

  %3$s

如果这不是您本人操作，请忽略此邮件。';

$Definition['EmailNotification'] = '%1$s

点击以下链接查看详情：
%2$s

祝您使用愉快！';

$Definition['EmailStoryNotification'] = '%1$s

%3$s

---
点击以下链接查看详情：
%2$s

祝您使用愉快！';

// 帮助文本
$Definition['PluginHelp'] = "插件可以为您的站点添加功能。<br />将插件添加到 %s 文件夹后，您可以在这里启用或禁用它。";
$Definition['ApplicationHelp'] = "应用可以为您的站点添加大量功能。<br />将应用添加到 %s 文件夹后，您可以在这里启用或禁用它。";
$Definition['ThemeHelp'] = "主题可以改变您站点的外观。<br />将主题添加到 %s 文件夹后，您可以在这里启用它。";
$Definition['AddonProblems'] = "<h2>遇到问题？</h2><p>如果某个插件出现问题导致您无法正常使用站点，您可以通过编辑以下文件手动禁用它们：</p>%s";

// 日期格式
$Definition['Date.DefaultFormat'] = '%Y年%m月%d日';
$Definition['Date.DefaultDayFormat'] = '%m月%d日';
$Definition['Date.DefaultYearFormat'] = '%Y年%m月';
$Definition['Date.DefaultTimeFormat'] = '%H:%M';
$Definition['Date.DefaultDateTimeFormat'] = '%Y年%m月%d日 %H:%M';

// 其他
$Definition['Saved'] = '您的更改已保存。';
$Definition['%s new plural'] = '%s 条新内容';
$Definition['TermsOfService'] = '服务条款';
$Definition['TermsOfServiceText'] =
    "您同意在使用本服务时，不会发布任何您明知是虚假和/或诽谤性、
不准确、辱骂、粗俗、仇恨、骚扰、淫秽、亵渎、色情、威胁、
侵犯他人隐私或其他违法的内容。您同意不发布任何受版权保护的材料，
除非版权归您所有。

我们保留在因您发布的任何消息引起投诉或法律诉讼时披露您身份
（或我们所知的任何关于您的信息）的权利。我们会记录所有访问
本网站的 IP 地址。

请注意，广告、连锁信、传销和招揽在本社区是不适当的。

我们保留以任何理由或无理由删除任何内容的权利。
我们保留以任何理由或无理由终止任何会员资格的权利。

您必须年满 13 周岁才能使用本服务。";

$Definition['Warning: This is for advanced users.'] = '<b>警告</b>：这是面向高级用户的功能，需要您对 Web 服务器进行额外配置。通常只有在您使用独立服务器或 VPS 时才可用。如果您不知道自己在做什么，请不要尝试此操作。';
$Definition['Activity.Delete'] = '×';
$Definition['Draft.Delete'] = '×';
$Definition['ConnectName'] = '用户名';

$Definition['Check out the new community forum I\'ve just set up.'] = '嗨，朋友！

来看看我刚建立的新社区论坛吧。这是一个我们可以在线交流的好地方。';

$Definition['Large images will be scaled down.'] = '大图片将被缩放，最大宽度 %spx，最大高度 %spx。';
$Definition['Locales allow you to support other languages on your site.'] =
    '语言包允许您的站点支持其他语言。在这里启用或禁用您想要提供的语言。';
$Definition['Test Email Message'] = '<p>这是一封测试邮件。</p>'.
    '<p>您可以在控制面板的邮件设置页面配置论坛邮件的外观。</p>';
$Definition['oauth2Instructions'] = '<p>通过填写您的 Client ID、Client Secret 和所需的端点，配置您的论坛以连接 OAuth2 应用。您可能需要向 SSO 应用提供允许的回调 URL 以验证请求。本论坛的回调 URL 是 <code>%s</code></p>';

// 常用界面文本
$Definition['Sign In'] = '登录';
$Definition['Sign Out'] = '退出';
$Definition['Register'] = '注册';
$Definition['Email'] = '邮箱';
$Definition['Password'] = '密码';
$Definition['Username'] = '用户名';
$Definition['Remember Me'] = '记住我';
$Definition['Forgot?'] = '忘记密码？';
$Definition['Forgot Password'] = '忘记密码';
$Definition['Reset Password'] = '重置密码';
$Definition['New Password'] = '新密码';
$Definition['Confirm Password'] = '确认密码';
$Definition['Save'] = '保存';
$Definition['Cancel'] = '取消';
$Definition['Delete'] = '删除';
$Definition['Edit'] = '编辑';
$Definition['Search'] = '搜索';
$Definition['Submit'] = '提交';
$Definition['Close'] = '关闭';
$Definition['Back'] = '返回';
$Definition['Next'] = '下一步';
$Definition['Previous'] = '上一步';
$Definition['More'] = '更多';
$Definition['Less'] = '收起';
$Definition['Yes'] = '是';
$Definition['No'] = '否';
$Definition['OK'] = '确定';
$Definition['Loading...'] = '加载中...';
$Definition['Please wait...'] = '请稍候...';

// 用户相关
$Definition['Profile'] = '个人主页';
$Definition['My Profile'] = '我的主页';
$Definition['Edit Profile'] = '编辑资料';
$Definition['Preferences'] = '偏好设置';
$Definition['Notifications'] = '通知';
$Definition['Activity'] = '动态';
$Definition['Inbox'] = '收件箱';
$Definition['Bookmarks'] = '收藏';
$Definition['My Bookmarks'] = '我的收藏';
$Definition['My Discussions'] = '我的帖子';
$Definition['My Drafts'] = '我的草稿';
$Definition['Joined'] = '加入时间';
$Definition['Visits'] = '访问次数';
$Definition['Last Active'] = '最后活跃';
$Definition['Online'] = '在线';
$Definition['Offline'] = '离线';

// 管理后台
$Definition['Dashboard'] = '管理后台';
$Definition['Settings'] = '设置';
$Definition['Moderation'] = '审核管理';
$Definition['Appearance'] = '外观';
$Definition['Users'] = '用户';
$Definition['Roles & Permissions'] = '角色与权限';
$Definition['Plugins'] = '插件';
$Definition['Themes'] = '主题';
$Definition['Locales'] = '语言';
$Definition['Configuration'] = '配置';
$Definition['Import'] = '导入';
$Definition['Export'] = '导出';
$Definition['Statistics'] = '统计';

// 角色
$Definition['Administrator'] = '管理员';
$Definition['Moderator'] = '版主';
$Definition['Member'] = '会员';
$Definition['Guest'] = '游客';
$Definition['Banned'] = '已封禁';
$Definition['Applicant'] = '申请者';

// 状态
$Definition['Active'] = '活跃';
$Definition['Inactive'] = '不活跃';
$Definition['Enabled'] = '已启用';
$Definition['Disabled'] = '已禁用';
$Definition['Pending'] = '待审核';
$Definition['Approved'] = '已通过';
$Definition['Denied'] = '已拒绝';

// 时间相关
$Definition['just now'] = '刚刚';
$Definition['%s seconds ago'] = '%s 秒前';
$Definition['%s minute ago'] = '%s 分钟前';
$Definition['%s minutes ago'] = '%s 分钟前';
$Definition['%s hour ago'] = '%s 小时前';
$Definition['%s hours ago'] = '%s 小时前';
$Definition['%s day ago'] = '%s 天前';
$Definition['%s days ago'] = '%s 天前';
$Definition['%s week ago'] = '%s 周前';
$Definition['%s weeks ago'] = '%s 周前';
$Definition['%s month ago'] = '%s 个月前';
$Definition['%s months ago'] = '%s 个月前';
$Definition['%s year ago'] = '%s 年前';
$Definition['%s years ago'] = '%s 年前';

// 页面元素
$Definition['Home'] = '首页';
$Definition['Categories'] = '板块';
$Definition['Discussions'] = '帖子';
$Definition['Recent Discussions'] = '最新帖子';
$Definition['All Discussions'] = '所有帖子';
$Definition['All Categories'] = '所有板块';
$Definition['Recent Activity'] = '最近动态';
$Definition['Howdy, Stranger!'] = '您好，访客！';
$Definition['It looks like you\'re new here. If you want to get involved, click one of these buttons!'] = '看起来您是新来的。如果您想参与，请点击以下按钮！';
$Definition['Quick Links'] = '快捷链接';

// 帖子相关
$Definition['Discussion'] = '帖子';
$Definition['New Discussion'] = '发帖';
$Definition['Start a New Discussion'] = '发帖';
$Definition['Post Discussion'] = '发布帖子';
$Definition['Edit Discussion'] = '编辑帖子';
$Definition['Delete Discussion'] = '删除帖子';
$Definition['Comment'] = '回复';
$Definition['Comments'] = '回复';
$Definition['Post Comment'] = '发表回复';
$Definition['Edit Comment'] = '编辑回复';
$Definition['Delete Comment'] = '删除回复';
$Definition['Quote'] = '引用';
$Definition['Reply'] = '回复';
$Definition['Write a comment...'] = '写下您的回复...';
$Definition['Body'] = '内容';
$Definition['Title'] = '标题';
$Definition['Category'] = '板块';
$Definition['Tags'] = '标签';
$Definition['Announce'] = '公告';
$Definition['Announcement'] = '公告';
$Definition['Close'] = '关闭';
$Definition['Closed'] = '已锁定';
$Definition['Sink'] = '下沉';
$Definition['Pin'] = '置顶';
$Definition['Pinned'] = '置顶';
$Definition['Bookmark'] = '收藏';
$Definition['Bookmarked'] = '已收藏';
$Definition['Unbookmark'] = '取消收藏';
$Definition['Flag'] = '举报';
$Definition['Spam'] = '垃圾信息';
$Definition['Move'] = '移动';
$Definition['Draft'] = '草稿';
$Definition['Save Draft'] = '保存草稿';
$Definition['Preview'] = '预览';
$Definition['Post'] = '发布';

// 统计
$Definition['views'] = '浏览';
$Definition['view'] = '浏览';
$Definition['%s view'] = '%s 次浏览';
$Definition['%s views'] = '%s 次浏览';
$Definition['comments'] = '回复';
$Definition['comment'] = '回复';
$Definition['%s comment'] = '%s 条回复';
$Definition['%s comments'] = '%s 条回复';
$Definition['discussions'] = '帖子';
$Definition['discussion'] = '帖子';
$Definition['%s discussion'] = '%s 个帖子';
$Definition['%s discussions'] = '%s 个帖子';
$Definition['Started by'] = '发起人';
$Definition['Most recent by'] = '最后回复';
$Definition['Last Post'] = '最后回复';
$Definition['First Post'] = '首帖';

// 搜索
$Definition['Search'] = '搜索';
$Definition['Search Results'] = '搜索结果';
$Definition['Enter your search term.'] = '输入搜索内容...';
$Definition['No results found.'] = '未找到结果。';
$Definition['Search for users'] = '搜索用户';

// 错误页面
$Definition['Page Not Found'] = '页面未找到';
$Definition['The page you were looking for could not be found.'] = '您要查找的页面不存在。';
$Definition['Permission Problem'] = '权限不足';
$Definition['You don\'t have permission to do that.'] = '您没有权限执行此操作。';

// 确认对话框
$Definition['Are you sure you want to delete this?'] = '确定要删除吗？';
$Definition['Are you sure you want to delete this discussion?'] = '确定要删除这个帖子吗？';
$Definition['Are you sure you want to delete this comment?'] = '确定要删除这条回复吗？';
$Definition['This action cannot be undone.'] = '此操作无法撤销。';

// ========== 登录注册页面 ==========
$Definition['Recover Password'] = '找回密码';
$Definition['Reset my password'] = '重置密码';
$Definition['I remember now!'] = '我想起来了！';
$Definition['AttemptingSignOut'] = '您正在尝试退出登录。确定要%s吗？';
$Definition['sign out'] = '退出';
$Definition['Or you can...'] = '或者您可以...';
$Definition['Keep me signed in'] = '保持登录状态';
$Definition["Don't have an account? %s"] = '还没有账号？%s';
$Definition['Create One.'] = '立即注册';
$Definition['Redirecting...'] = '正在跳转...';
$Definition['Confirm Email'] = '验证邮箱';
$Definition['Email Unavailable'] = '该邮箱已被使用';
$Definition['Name Unavailable'] = '该用户名已被使用';
$Definition['Your password must be at least %d characters long.'] = '密码长度至少需要 %d 个字符。';
$Definition['For a stronger password, increase its length or combine upper and lowercase letters, digits, and symbols.'] = '为了提高密码强度，请增加长度或混合使用大小写字母、数字和符号。';
$Definition['Thank You!'] = '感谢您！';
$Definition['Your application will be reviewed by an administrator. You will be notified by email if your application is approved.'] = '您的申请将由管理员审核。审核通过后我们会发送邮件通知您。';
$Definition['Resetting the password for %s.'] = '正在重置 %s 的密码。';
$Definition['Request another password reset.'] = '重新发送密码重置邮件';
$Definition['%s Connect'] = '%s 登录';
$Definition['Profile Picture'] = '头像';
$Definition['You are connected as %s through %s.'] = '您已通过 %s 以 %s 身份登录。';
$Definition['You are connected as %s.'] = '您已以 %s 身份登录。';
$Definition['You are connected through %2$s.'] = '您已通过 %2$s 登录。';
$Definition['You are now signed in.'] = '您已成功登录。';
$Definition['Other'] = '其他';
$Definition['Your email has been successfully confirmed.'] = '您的邮箱已成功验证。';
$Definition['You can either create a new account, or enter your credentials if you have an existing account.'] = '您可以创建新账号，或使用已有账号登录。';
$Definition['Give me a new account'] = '创建新账号';
$Definition['Link my existing account'] = '使用已有账号';
$Definition['Register for Membership'] = '注册会员';
$Definition['Registration is currently closed.'] = '注册功能已关闭。';
$Definition['A message has been sent to your email address with password reset instructions.'] = '密码重置邮件已发送到您的邮箱。';
$Definition['Account Sync Failed'] = '账号同步失败';

// ========== 个人资料页面 ==========
$Definition['General'] = '通用';
$Definition['Notification'] = '通知';
$Definition['By uploading a file you certify that you have the right to distribute this picture and that it does not violate the Terms of Service.'] = '上传文件即表示您确认拥有该图片的分发权，且不违反服务条款。';
$Definition['Upload New Picture'] = '上传新头像';
$Definition['Remove Picture'] = '移除头像';
$Definition['Connected'] = '已连接';
$Definition['Connect'] = '连接';
$Definition['Allow other members to see your email?'] = '允许其他会员查看您的邮箱？';
$Definition['Confirmed email address'] = '已验证邮箱';
$Definition['Notifications will appear here.'] = '通知将显示在这里。';
$Definition['You do not have any notifications yet.'] = '您还没有任何通知。';
$Definition['Change My Password'] = '修改密码';
$Definition['Invitations'] = '邀请';
$Definition['You have %s invitations left for this month.'] = '您本月还剩 %s 次邀请机会。';
$Definition['On'] = '时间';
$Definition['Status'] = '状态';
$Definition['Expires'] = '过期时间';
$Definition['Uninvite'] = '撤销邀请';
$Definition['Send Again'] = '重新发送';
$Definition['Remove'] = '移除';
$Definition['Accepted'] = '已接受';
$Definition['Enter Your Password'] = '请输入密码';
$Definition['Verified'] = '已验证';
$Definition['Not Verified'] = '未验证';
$Definition['access token'] = '访问令牌';
$Definition['This is a system account and does not represent a real person.'] = '这是一个系统账户，不代表真实用户。';
$Definition['clear'] = '清除';
$Definition['Personal Access Tokens'] = '个人访问令牌';
$Definition['Generate New Token'] = '生成新令牌';
$Definition['Reveal'] = '显示';
$Definition['Picture Removed'] = '头像已移除';
$Definition['Picture was successfully removed.'] = '头像已成功移除。';
$Definition['Basic Information'] = '基本信息';
$Definition['Roles'] = '角色';
$Definition['Invited by'] = '邀请人';

// ========== 活动相关 ==========
$Definition['%s commented on %s.'] = '%s 回复了 %s。';
$Definition['%s started a new discussion %s.'] = '%s 发起了新帖子 %s。';
$Definition['%s mentioned %s in a %s.'] = '%s 在%s中提到了 %s。';
$Definition['%s liked %s.'] = '%s 赞了 %s。';
$Definition['%s was tagged in %s.'] = '%s 在 %s 中被提及。';
$Definition['New Comment'] = '新回复';
$Definition['New Discussion'] = '新帖子';
$Definition['New Mention'] = '新提及';

// ========== 编辑器相关 ==========
$Definition['Write'] = '写作';
$Definition['Bold'] = '粗体';
$Definition['Italic'] = '斜体';
$Definition['Strikethrough'] = '删除线';
$Definition['Link'] = '链接';
$Definition['Image'] = '图片';
$Definition['Code'] = '代码';
$Definition['Spoiler'] = '剧透';
$Definition['Emoji'] = '表情';
$Definition['Insert Image'] = '插入图片';
$Definition['Insert Link'] = '插入链接';
$Definition['URL'] = '网址';
$Definition['Enter your url'] = '输入网址';
$Definition['Attach File'] = '附件';
$Definition['Upload'] = '上传';
$Definition['Uploading...'] = '上传中...';
$Definition['File Upload Error'] = '文件上传失败';
$Definition['Formatting'] = '格式化';
$Definition['Paragraph'] = '段落';
$Definition['Heading'] = '标题';
$Definition['Blockquote'] = '引用';
$Definition['Code Block'] = '代码块';
$Definition['Bulleted List'] = '无序列表';
$Definition['Numbered List'] = '有序列表';

// ========== 表单验证 ==========
$Definition['This field is required.'] = '此字段必填。';
$Definition['Please enter a valid email address.'] = '请输入有效的邮箱地址。';
$Definition['Passwords do not match.'] = '两次输入的密码不一致。';
$Definition['Password is too short.'] = '密码太短。';
$Definition['Username is already taken.'] = '用户名已被占用。';
$Definition['Email is already in use.'] = '邮箱已被使用。';

// ========== 后台设置页面 ==========
$Definition['Site Settings'] = '站点设置';
$Definition['Branding'] = '站点设置';
$Definition['Banner'] = '横幅';
$Definition['Logo'] = 'Logo';
$Definition['Favicon'] = '网站图标';
$Definition['Homepage'] = '首页';
$Definition['Email Settings'] = '邮件设置';
$Definition['Outgoing Email'] = '发送邮件';
$Definition['SMTP'] = 'SMTP';
$Definition['Routes'] = '路由';
$Definition['Bans'] = '封禁列表';
$Definition['Add Ban'] = '添加封禁';
$Definition['Edit Ban'] = '编辑封禁';
$Definition['Ban Type'] = '封禁类型';
$Definition['Ban Value'] = '封禁值';
$Definition['Banned Users'] = '已封禁用户';
$Definition['Registration'] = '注册设置';
$Definition['Registration Method'] = '注册方式';
$Definition['Basic'] = '开放注册';
$Definition['Approval'] = '需要审核';
$Definition['Invitation'] = '仅限邀请';
$Definition['Closed'] = '关闭注册';

// ========== 主题和插件 ==========
$Definition['Current Theme'] = '当前主题';
$Definition['Mobile Theme'] = '移动端主题';
$Definition['Preview'] = '预览';
$Definition['Apply'] = '应用';
$Definition['Theme Options'] = '主题选项';
$Definition['Plugin Options'] = '插件选项';
$Definition['Enable'] = '启用';
$Definition['Disable'] = '禁用';
$Definition['Plugin'] = '插件';
$Definition['Theme'] = '主题';
$Definition['Version'] = '版本';
$Definition['Author'] = '作者';
$Definition['Description'] = '描述';

// ========== 用户管理 ==========
$Definition['Add User'] = '添加用户';
$Definition['Edit User'] = '编辑用户';
$Definition['Delete User'] = '删除用户';
$Definition['Ban User'] = '封禁用户';
$Definition['Unban User'] = '解封用户';
$Definition['User List'] = '用户列表';
$Definition['Search Users'] = '搜索用户';
$Definition['Filter'] = '筛选';
$Definition['All Users'] = '所有用户';
$Definition['New Users'] = '新用户';
$Definition['Applicants'] = '待审核';
$Definition['Approve'] = '通过';
$Definition['Decline'] = '拒绝';
$Definition['Merge Users'] = '合并用户';
$Definition['Change Password'] = '修改密码';
$Definition['Send Password Reset Email'] = '发送密码重置邮件';

// ========== 分类管理 ==========
$Definition['Add Category'] = '添加板块';
$Definition['Edit Category'] = '编辑板块';
$Definition['Delete Category'] = '删除板块';
$Definition['Category Name'] = '板块名称';
$Definition['Category Url'] = '板块网址';
$Definition['Parent Category'] = '父级板块';
$Definition['Display As'] = '显示方式';
$Definition['Discussions Layout'] = '帖子布局';
$Definition['Categories Layout'] = '板块布局';
$Definition['Category Permissions'] = '板块权限';
$Definition['Allow File Uploads'] = '允许上传文件';
$Definition['Archive Discussions'] = '归档帖子';

// ========== 日志和审核 ==========
$Definition['Moderation Log'] = '审核日志';
$Definition['Edit Log'] = '编辑日志';
$Definition['Spam Log'] = '垃圾日志';
$Definition['Change Log'] = '变更日志';
$Definition['Restore'] = '恢复';
$Definition['Delete Forever'] = '永久删除';
$Definition['Verify'] = '验证';
$Definition['Not Spam'] = '非垃圾';
$Definition['Report'] = '举报';
$Definition['Reported By'] = '举报人';
$Definition['Reported Date'] = '举报时间';
$Definition['Reason'] = '原因';

// ========== 消息相关 ==========
$Definition['New Message'] = '新私信';
$Definition['Inbox'] = '收件箱';
$Definition['Sent'] = '已发送';
$Definition['To'] = '收件人';
$Definition['Subject'] = '主题';
$Definition['Message'] = '消息';
$Definition['Send Message'] = '发送私信';
$Definition['Reply to Message'] = '回复私信';
$Definition['Leave Conversation'] = '离开对话';
$Definition['Add People'] = '添加成员';

// ========== 页面标题 ==========
$Definition['Getting Started'] = '新手入门';
$Definition['Site Overview'] = '站点概览';
$Definition['Recent Activity'] = '最近动态';
$Definition['Active Users'] = '活跃用户';
$Definition['New Registrations'] = '新注册';
$Definition['Total Users'] = '总用户数';
$Definition['Total Discussions'] = '总帖子数';
$Definition['Total Comments'] = '总回复数';
