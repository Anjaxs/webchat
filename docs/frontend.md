# 前端文档

## 主要文件和目录说明

- client/  所有前端代码都在这个目录
- client/config/  存放配置文件
- client/src 前端业务代码


### client/src 目录下的文件说明

- api/axios.js  请求拦截器
- api/server.js  定义请求接口的地方
- main.js  定义接收 socket 事件的逻辑
- socket-handle.js  发送 socket 事件
- store/index.js  几乎所有处理数据的逻辑都在这

## 问题

1. q: 如何修改后端服务url  
a: 在 client/vue.config.js 修改 devServer 的 '/api' target
