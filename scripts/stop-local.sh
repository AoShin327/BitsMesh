#!/bin/bash
# 停止本地 Vanilla 开发服务

echo "🛑 停止 Vanilla 本地开发环境..."

# 注意: 这会停止所有 Homebrew 管理的服务
# 如果有其他项目也在用，请谨慎操作

read -p "是否停止 nginx, php, mysql 服务? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    brew services stop nginx
    brew services stop php
    brew services stop mysql
    echo "✅ 服务已停止"
else
    echo "❌ 已取消"
fi
