# Profile Extender 插件文档

[根目录](../../CLAUDE.md) > [plugins](../) > **ProfileExtender**

---

## 模块职责
允许管理员为用户资料和注册表单添加自定义字段（如状态、位置、游戏标签等）。

---

## 核心功能

### 1. 字段管理
- 添加/编辑/删除自定义字段
- 支持多种字段类型：文本、下拉、复选框、日期等
- 字段排序和分组

### 2. 字段显示位置
- 用户资料页
- 注册表单
- 用户列表

### 3. 字段验证
- 必填项设置
- 格式验证
- 长度限制

---

## 设置页面
- **路径**：`/dashboard/settings/profileextender`
- **权限**：`Garden.Settings.Manage`

---

## 视图文件
- `views/settings.php` - 插件设置
- `views/addedit.php` - 添加/编辑字段
- `views/delete.php` - 删除确认
- `views/registrationfields.php` - 注册表单字段
- `views/profilefields.php` - 资料页字段
- `views/helper_functions.php` - 辅助函数

---

## 前端脚本
- `js/profileextender.js` - 字段交互

---

## 数据模型
自定义字段存储在用户属性（User->Attributes）中：
```php
[
    'ProfileExtender' => [
        'Location' => 'Beijing',
        'Status' => 'Active',
        'CustomField' => '...'
    ]
]
```

---

## 常见问题

### Q1: 如何添加自定义字段？
访问设置页面 > Add Field > 配置字段属性 > 保存。

### Q2: 字段可以设置为必填吗？
可以，在字段设置中勾选"Required"选项。

### Q3: 如何在主题中显示自定义字段？
```php
$value = valr('Attributes.ProfileExtender.FieldName', $user);
```

---

## 相关文件清单
```
plugins/ProfileExtender/
├── addon.json
├── class.profileextender.plugin.php
├── js/profileextender.js
├── views/
│   ├── settings.php
│   ├── addedit.php
│   ├── delete.php
│   ├── registrationfields.php
│   ├── profilefields.php
│   └── helper_functions.php
├── icon.png
└── profile-extender.png
```

---

**最后更新**：2026-01-17 20:57:17 | **版本**：3.0.2
