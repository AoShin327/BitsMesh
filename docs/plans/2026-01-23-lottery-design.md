# 幸运抽奖功能设计文档

**日期**: 2026-01-23
**状态**: 已确认，待实现

---

## 概述

基于 Cloudflare drand 随机信标的公平抽奖工具，纯前端实现，支持可验证的确定性抽奖。

### 核心特点

- **纯前端实现** - 无需后端存储
- **可验证公平** - 同样参数生成同样结果
- **链接唯一性** - 抽奖信息编码在 URL 中
- **自动获取数据** - 从帖子 API 获取评论列表

---

## 用户流程

### 1. 创建抽奖

1. 用户发布抽奖帖子，公布规则（开奖时间、奖品数等）
2. 访问 `/lottery` 页面
3. 填写配置表单：
   - 帖子链接
   - 开奖时间
   - 中奖人数
   - 起始楼层（可选）
   - 是否排除重复评论（可选）
4. 生成唯一抽奖链接
5. 将链接补充到帖子中

### 2. 查看抽奖

1. 访问抽奖链接
2. 未到时间：显示倒计时 + 当前参与人数
3. 已到时间：自动计算并显示中奖名单
4. 可一键复制 @消息字符串

---

## 技术设计

### URL 参数

```
/lottery?post=123&time=1737817200&count=3&start=1&unique=1
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `post` | int | ✅ | 帖子 ID |
| `time` | int | ✅ | 开奖时间戳（秒）|
| `count` | int | ✅ | 中奖人数 |
| `start` | int | ❌ | 起始楼层（默认 1）|
| `unique` | 0/1 | ❌ | 排除重复用户（默认 1）|

### 抽奖算法

```
1. 解析 URL 参数
       ↓
2. 调用 Vanilla API 获取评论列表
   GET /api/v2/comments?discussionID={post}&limit=500
       ↓
3. 过滤有效评论
   - 楼层 >= start
   - 发布时间 < 开奖时间
   - unique=1 时同一用户只保留第一条
       ↓
4. 判断是否已到开奖时间
   - 未到：显示倒计时
   - 已到：继续步骤 5
       ↓
5. 计算 drand round 号
   round = floor((开奖时间戳 - 1595431050) / 30)
       ↓
6. 获取 drand 随机信标
   GET https://api.drand.sh/{CHAIN}/public/{round}
   → 获取 randomness (hex string)
       ↓
7. 调用 random.org 生成全排列（或本地 PRNG 备用）
   GET https://www.random.org/sequences/
       ?min=1&max={楼层数}&col=1&format=plain
       &rnd=id.{randomness}
       ↓
8. 从全排列中按顺序选取中奖楼层
   直到达到 count 或队列为空
       ↓
9. 按楼层号排序并显示结果
```

### 关键常量

```javascript
// drand mainnet chain hash
const DRAND_CHAIN = '8990e7a9aaed2ffed73dbd7092123d6f289930540d7651336225dc172e51b2ce';

// drand genesis time (2020-07-22 14:50:50 UTC)
const DRAND_GENESIS = 1595431050;

// drand round period (30 seconds)
const DRAND_PERIOD = 30;
```

### 备用随机方案

当 random.org 不可用时，使用本地 seeded PRNG：

```javascript
function seededShuffle(array, seed) {
    function mulberry32(a) {
        return function() {
            let t = a += 0x6D2B79F5;
            t = Math.imul(t ^ t >>> 15, t | 1);
            t ^= t + Math.imul(t ^ t >>> 7, t | 61);
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        }
    }

    const seedNum = parseInt(seed.slice(0, 8), 16);
    const random = mulberry32(seedNum);

    const result = [...array];
    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(random() * (i + 1));
        [result[i], result[j]] = [result[j], result[i]];
    }
    return result;
}
```

---

## 界面设计

### 配置表单

```
┌─────────────────────────────────────────────────────┐
│  🎲 幸运抽奖工具                                      │
├─────────────────────────────────────────────────────┤
│  帖子链接 *                                          │
│  ┌─────────────────────────────────────────────┐    │
│  │ https://example.com/post-123                │    │
│  └─────────────────────────────────────────────┘    │
│                                                      │
│  开奖时间 *                                          │
│  ┌──────────────────┐  ┌─────────────┐              │
│  │ 2026-01-25       │  │ 20:00       │              │
│  └──────────────────┘  └─────────────┘              │
│                                                      │
│  中奖人数 *              起始楼层                     │
│  ┌─────────┐            ┌─────────┐                 │
│  │ 3       │            │ 1       │                 │
│  └─────────┘            └─────────┘                 │
│                                                      │
│  ☑ 排除同一用户的重复评论                             │
│                                                      │
│  ┌─────────────────────────────────────────────┐    │
│  │           🔗 生成抽奖链接                    │    │
│  └─────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

### 抽奖结果

```
┌─────────────────────────────────────────────────────┐
│  🎉 抽奖结果                                         │
├─────────────────────────────────────────────────────┤
│  帖子：Welcome to awesome!                          │
│  开奖时间：2026-01-25 20:00:00                      │
│  中奖人数：3 人                                      │
│                                                      │
│  🏆 中奖名单                                         │
│  ┌─────────────────────────────────────────────┐    │
│  │  #12  UserA     │  查看评论 →                │    │
│  │  #34  UserB     │  查看评论 →                │    │
│  │  #45  UserC     │  查看评论 →                │    │
│  └─────────────────────────────────────────────┘    │
│                                                      │
│  📋 复制 @消息                                       │
│  ┌─────────────────────────────────────────────┐    │
│  │ @UserA @UserB @UserC 恭喜中奖！              │ 📋 │
│  └─────────────────────────────────────────────┘    │
│                                                      │
│  🔍 验证信息                                         │
│  drand round: 123456789                             │
│  randomness: a1b2c3d4...                            │
└─────────────────────────────────────────────────────┘
```

---

## 文件结构

```
themes/bitsmesh/
├── views/
│   └── lottery.php              # 抽奖页面模板
├── js/
│   └── lottery.js               # 抽奖核心逻辑
├── design/
│   └── bits-lottery.css         # 抽奖页面样式
└── class.bitsmesh.themehooks.php # 添加路由
```

### 路由配置

```php
// /lottery → /profile/lottery
case 'lottery':
    $request->setPathPart(0, 'profile');
    $request->setPathPart(1, 'lottery');
    break;
```

### 控制器方法

```php
public function profileController_lottery_create($sender) {
    $sender->permission('Garden.SignIn.Allow');
    $sender->title(t('Lucky Draw', '幸运抽奖'));
    $sender->render('lottery', '', 'themes/bitsmesh');
}
```

---

## 错误处理

| 场景 | 处理方式 |
|------|----------|
| 帖子不存在 | 提示"帖子不存在或已被删除" |
| 无评论 | 提示"该帖子暂无评论" |
| 有效评论不足 | 提示将全部中奖 |
| drand 未到时间 | 显示倒计时 |
| API 失败 | 显示重试按钮 |
| random.org 失败 | 使用本地 PRNG 备用 |

---

## 验证信息

结果页显示完整验证信息，确保任何人可验证：

- 开奖时间戳
- drand round 号
- randomness 值
- 随机源类型
- 有效评论数
- 参与用户数

---

## 实现任务

1. [ ] 创建 `lottery.php` 页面模板
2. [ ] 创建 `lottery.js` 核心逻辑
3. [ ] 创建 `bits-lottery.css` 样式
4. [ ] 添加路由配置到 themehooks
5. [ ] 更新侧边栏链接
6. [ ] 测试完整流程

---

**设计确认**: 2026-01-23
