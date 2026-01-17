# Vanilla 3.3 本地开发与生产部署方案

**创建日期**：2026-01-17
**状态**：设计完成，待实施

---

## 1. 项目概述

将 Vanilla 3.3 深度定制版本（含 NodeSeek/BitsMesh 主题）配置为：
- 本地开发环境，运行在 `http://localhost:8357/`
- 支持 Playwright 自动化测试
- 通过 Git 部署到生产 Linux VPS

### 1.1 核心决策

| 项目 | 决定 |
|------|------|
| **版本控制** | 完整 Fork，整个项目纳入 Git |
| **本地 Web 服务器** | Nginx + PHP-FPM (Homebrew) |
| **本地数据库** | MySQL 5.7 (Homebrew) |
| **生产服务器** | Linux VPS + Nginx + PHP + MySQL 5.7+ |
| **部署方式** | Git Pull |
| **升级策略** | 不需要（官方已停止更新） |

---

## 2. 环境配置

### 2.1 本地环境

| 组件 | 版本 | 安装方式 |
|------|------|---------|
| Nginx | latest | `brew install nginx` |
| PHP | 8.2 | `brew install php` |
| MySQL | 5.7 | `brew install mysql@5.7` |
| 访问地址 | - | `http://localhost:8357` |

### 2.2 生产环境

| 组件 | 版本 | 说明 |
|------|------|------|
| OS | Linux | VPS 完全控制 |
| Nginx | latest | 系统包管理器安装 |
| PHP | 8.x | 系统包管理器安装 |
| MySQL | 5.7+ | 系统包管理器安装 |

---

## 3. 目录结构

```
/Users/kilmu/Dev/WebDev/vanilla/
├── .git/                            # Git 仓库
├── .gitignore                       # 忽略规则
├── conf/
│   ├── config.php                   # 本地配置 (git忽略)
│   └── config.php.example           # 配置模板
├── themes/nodeseek/                 # BitsMesh 主题
│   └── tests/                       # Playwright 测试
├── uploads/                         # 用户上传 (git忽略)
├── cache/                           # 缓存目录 (git忽略)
└── scripts/                         # 运维脚本
    ├── start-local.sh               # 启动本地服务
    ├── stop-local.sh                # 停止本地服务
    └── test.sh                      # 运行测试
```

---

## 4. Git 配置

### 4.1 .gitignore

```gitignore
# 环境配置 - 每个环境不同
conf/config.php

# 用户数据 - 不应提交
uploads/*
!uploads/.gitkeep

# 缓存 - 自动生成
cache/*
!cache/.gitkeep

# 系统文件
.DS_Store
*.log

# IDE
.idea/
.vscode/

# 依赖
/vendor/

# 测试产物
themes/nodeseek/tests/visual/*.png
themes/nodeseek/tests/test-results/
themes/nodeseek/tests/playwright-report/
node_modules/
```

### 4.2 分支策略

- `main` - 生产就绪代码
- `dev` - 开发中功能（可选）

---

## 5. Nginx 配置

### 5.1 本地配置

文件位置：`/opt/homebrew/etc/nginx/servers/vanilla.conf`

```nginx
server {
    listen 8357;
    server_name localhost;
    root /Users/kilmu/Dev/WebDev/vanilla;
    index index.php;

    # Vanilla URL 重写规则
    location / {
        try_files $uri $uri/ /index.php?p=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问敏感文件
    location ~ /\.ht { deny all; }
    location ~ /conf/ { deny all; }
}
```

### 5.2 生产配置模板

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/vanilla;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Gzip 压缩
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    # 静态资源缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location / {
        try_files $uri $uri/ /index.php?p=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht { deny all; }
    location ~ /conf/ { deny all; }
}
```

---

## 6. 部署流程

### 6.1 首次部署（生产）

```bash
# 1. 克隆仓库
cd /var/www
git clone <仓库地址> vanilla
cd vanilla

# 2. 设置权限
chmod -R 755 .
chmod -R 777 conf/ cache/ uploads/
chown -R www-data:www-data .

# 3. 配置 Nginx 并重启
sudo ln -s /var/www/vanilla/scripts/nginx-prod.conf /etc/nginx/sites-enabled/vanilla.conf
sudo systemctl reload nginx

# 4. 访问域名 → Vanilla 安装向导
#    - 填写数据库信息
#    - 创建管理员账号
#    - 自动生成 conf/config.php

# 5. 启用 NodeSeek 主题
#    后台 → 外观 → 主题 → 选择 NodeSeek
```

### 6.2 日常更新

```bash
# 本地
git add .
git commit -m "feat: 功能描述"
git push origin main

# 生产
cd /var/www/vanilla
git pull origin main
rm -rf cache/*
```

---

## 7. 开发工作流

```
修改代码 → 刷新浏览器 → Playwright 测试 → git push → 生产 git pull
```

### 7.1 测试命令

```bash
cd themes/nodeseek/tests
npm run test:e2e      # E2E 测试
npm run test:visual   # 视觉回归测试
npm run test:update   # 更新截图基准
```

---

## 8. 实施清单

- [ ] 安装 Homebrew 组件（nginx, php, mysql@5.7）
- [ ] 创建本地数据库 vanilla_dev
- [ ] 配置本地 Nginx
- [ ] 初始化 Git 仓库
- [ ] 创建 .gitignore
- [ ] 创建 conf/config.php.example
- [ ] 创建 scripts/ 目录和脚本
- [ ] 本地访问安装向导完成安装
- [ ] 启用 NodeSeek 主题
- [ ] 运行 Playwright 测试验证
- [ ] 首次提交到远程仓库

---

**文档版本**：1.0.0
**最后更新**：2026-01-17
