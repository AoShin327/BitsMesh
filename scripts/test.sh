#!/bin/bash
# 运行 Playwright 测试

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TEST_DIR="$SCRIPT_DIR/../themes/nodeseek/tests"

cd "$TEST_DIR" || exit 1

echo "🧪 运行 Vanilla 主题测试..."
echo ""

# 检查依赖是否安装
if [ ! -d "node_modules" ]; then
    echo "📦 安装测试依赖..."
    npm install
fi

# 运行测试
echo "▶️  运行 E2E 测试..."
npm run test:e2e

if [ $? -eq 0 ]; then
    echo ""
    echo "▶️  运行视觉回归测试..."
    npm run test:visual
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ 所有测试通过！"
else
    echo ""
    echo "❌ 测试失败，请检查输出"
    exit 1
fi
