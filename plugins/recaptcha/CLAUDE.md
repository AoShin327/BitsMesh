# reCAPTCHA 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **recaptcha**

---

## 模块职责
在用户注册页面添加 Google reCAPTCHA 验证码，防止机器人注册。

---

## 核心功能

### 1. 注册验证
- 在注册表单添加 reCAPTCHA 控件
- 验证用户为真人后允许注册

### 2. reCAPTCHA API
- 支持 reCAPTCHA v2
- 需要配置 Site Key 和 Secret Key

---

## 配置项
```php
$Configuration['Plugins.Recaptcha.SiteKey'] = '...';
$Configuration['Plugins.Recaptcha.SecretKey'] = '...';
```

### 获取 API Key
访问 [Google reCAPTCHA](https://www.google.com/recaptcha/admin) 注册获取密钥。

---

## 视图文件
- `views/settings/registration.php` - 设置页面
- `views/display/captcha.php` - 验证码显示

---

## 常见问题

### Q1: 如何获取 reCAPTCHA 密钥？
访问 [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin) 创建站点并获取密钥。

### Q2: 支持 reCAPTCHA v3 吗？
当前版本为 v2，需要修改代码支持 v3。

### Q3: 本地开发如何测试？
在 reCAPTCHA 管理界面添加 localhost 域名。

---

## 相关文件清单
```
plugins/recaptcha/
├── addon.json
├── class.recaptcha.plugin.php
├── views/
│   ├── settings/registration.php
│   └── display/captcha.php
└── recaptcha_support.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：0.1
