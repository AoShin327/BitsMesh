[根目录](../../CLAUDE.md) > [plugins](../) > **swagger-ui**

---

# Swagger UI 插件

## 变更记录

| 日期 | 变更内容 |
|------|---------|
| 2026-01-17 20:48:21 | 初始化模块文档 |

---

## 模块职责

**Swagger UI** 插件为 Vanilla Forums 的 REST API 提供交互式文档界面。

**核心功能**：
- 自动生成 API 文档
- 交互式 API 测试
- 支持 OpenAPI 3.0 规范
- Token 认证支持

---

## 入口与启动

### 插件配置
**文件**：`addon.json`

```json
{
  "key": "swagger-ui",
  "name": "Swagger UI",
  "type": "addon"
}
```

### 主要文件
- **后端插件类**：`SwaggerUIPlugin.php`
- **前端入口**：`src/scripts/mountSwagger.ts`
- **视图**：`views/settings/swagger.php`

---

## 对外接口

### 访问路径
- **API 文档**：`/api/v2/docs`
- **OpenAPI JSON**：`/api/v2/open-api/v3`

### 使用方式
1. 访问 `/api/v2/docs`
2. 点击 "Authorize" 输入 API Token
3. 展开端点并点击 "Try it out"
4. 测试 API 调用

---

## 关键依赖与配置

### 前端依赖
- **swagger-ui-react**：Swagger UI 核心
- **TypeScript**

### 配置项
```php
// 默认启用
$Configuration['EnabledPlugins']['swagger-ui'] = true;
```

---

## 常见问题 (FAQ)

### Q1: 如何生成 API Token？
**A**: 访问 `/settings/tokens` 创建新 Token。

### Q2: 如何添加自定义 API 端点到文档？
**A**: API 端点通过注解自动扫描，确保控制器继承 `AbstractApiController` 并使用 OpenAPI 注解。

---

## 相关文件清单

```
swagger-ui/
├── addon.json
├── SwaggerUIPlugin.php
├── src/
│   └── scripts/
│       └── mountSwagger.ts
├── views/
│   └── settings/
│       └── swagger.php
└── yarn.lock
```

---

**最后更新**：2026-01-17 20:48:21
