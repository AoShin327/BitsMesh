# BitsMesh

基于 Vanilla Forums 3.3 深度定制的现代论坛系统。

## 特性

### 主题定制
- **现代化 UI**：简洁、扁平化的界面设计
- **楼层系统**：支持 `/post-{id}#{floor}` 短链接格式
- **回复引用**：`@用户 #楼层` 格式的回复功能
- **响应式布局**：适配桌面端与移动端
- **深色模式**：支持明暗主题切换

### 功能增强
- **积分系统**：Credits 插件（开发中）
- **图片灯箱**：点击图片放大预览
- **返回顶部**：滚动时显示的快捷按钮
- **移动端导航**：汉堡菜单适配

## 技术栈

| 组件 | 技术 |
|------|------|
| 后端 | PHP 8.2 |
| 数据库 | MySQL (utf8mb4) |
| 前端 | Vanilla JS, CSS3 |
| 图标 | IconPark |

## 安装

### 环境要求
- PHP >= 7.4（推荐 8.2）
- MySQL >= 5.7 / MariaDB >= 10.2
- Apache / Nginx

### 部署步骤

1. 克隆仓库
```bash
git clone https://github.com/AoShin327/BitsMesh.git
cd BitsMesh
```

2. 配置数据库
```bash
cp conf/config-defaults.php conf/config.php
# 编辑 conf/config.php 填写数据库信息
```

3. 设置目录权限
```bash
chmod -R 755 cache/ uploads/ conf/
```

4. 访问安装向导
```
http://your-domain/index.php?p=/setup
```

## 目录结构

```
BitsMesh/
├── applications/          # 核心应用
│   ├── dashboard/        # 管理后台
│   ├── vanilla/          # 论坛核心
│   └── conversations/    # 私信系统
├── plugins/              # 插件
│   └── Credits/          # 积分系统
├── themes/
│   └── bitsmesh/         # 主题文件
│       ├── design/       # CSS 样式
│       ├── js/           # JavaScript
│       └── views/        # 模板覆盖
├── library/              # 框架核心
└── conf/                 # 配置文件
```

## 开发

### 本地开发
```bash
# PHP 内置服务器
php -S localhost:8357 router.php
```

### 代码规范
- PHP: PSR-2 / PSR-12
- 提交信息: Conventional Commits

## 许可证

GPL-2.0-only

## 致谢

基于 [Vanilla Forums](https://github.com/vanilla/vanilla) 开源项目。
