#!/bin/bash
# 启动本地 Vanilla 开发服务

echo "🚀 启动 Vanilla 本地开发环境..."

# 检查服务状态
check_service() {
    if pgrep -x "$1" > /dev/null; then
        echo "✅ $1 已运行"
    else
        echo "⚠️  $1 未运行，正在启动..."
        brew services start "$2"
    fi
}

check_service "nginx" "nginx"
check_service "php-fpm" "php"
check_service "mysqld" "mysql"

echo ""
echo "🌐 访问地址: http://localhost:8357"
echo "📁 项目目录: /Users/kilmu/Dev/WebDev/vanilla"
echo ""
echo "按 Ctrl+C 不会停止服务，使用 ./stop-local.sh 停止"
