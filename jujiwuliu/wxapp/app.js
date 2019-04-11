var util = require('jujiwuliu/resource/js/util.js');
var webSocket = require('jujiwuliu/resource/js/webSocket.js');
var siteInfo = require('siteinfo.js');
var html2json = require('jujiwuliu/resource/js/htmlToWxml.js');
App({
    //加载巨吉物流工具类
    html2json: html2json,
    util: util,
    webSocket:webSocket,
    onLaunch: function () {
      var that = this;
      var options={};
      if (that.siteInfo.debug){
        options.url = that.siteInfo.debug_SocketUrl;//Socket 连接地址
      }else{
        options.url = that.siteInfo.SocketUrl;//Socket 连接地址
      }
      // 创建 Socket 连接
      that.webSocket.connectSocket(options);
      // 设置接收消息回调
      that.webSocket.onSocketMessageCallback = that.onSocketMessageCallback;
      that.webSocket.heartBeatAccessory = that.heartBeatAccessory;//心跳附件
      
      //先获取用户信息缓存 然后把用户信息发送给socket 以启动守子进程
      this.user_verify();
    },
    user_verify:function(){
      var that=this;
      var user_info = wx.getStorageSync('user_info');
      var userType = wx.getStorageSync('userType');
      if (user_info.uid && userType){

        var obj = {};
        obj.scene = 'user';//处理模型 前缀（场景）
        obj.type = 'verify';//处理方法
        obj.uniacid = that.siteInfo.uniacid;
        obj.uid = user_info.uid;
        
        that.webSocket.sendSocketMessage({
          msg: JSON.stringify(obj) ,
            success: function (res) {
              console.log('socket 用户信息发送成功');
            },
            fail: function (res) {
              console.log('socket 用户信息发送失败');
              if (heartBeatFailCount > 2) {
                // 重连
                self.connectSocket();
              }
            
              heartBeatFailCount++;
            },

        });//发送数据

      }else{
        setTimeout(function(){
          that.user_verify();
        },1000)
      }

    },
    heartBeatAccessory:function(){
      var that = this;
      //获取地理位置
      // wx.getLocation({
      //   type: 'wgs84',
      //   success(res) {
      //     const latitude = res.latitude
      //     const longitude = res.longitude
      //     const speed = res.speed
      //     const accuracy = res.accuracy

      //   },fail:function(){
           
      //   }
      // })

    },
    onSocketMessageCallback:function(data){
      //这里是Socket 收到后台主动推送的消息业务处理方法
      var that=this;
      data = JSON.parse(data)
      console.log(data)
      wx.showModal({
          'title':'温馨提示',
          'content': data.msg,
          'showCancel':false,
          'success':function(){
            var userType = wx.getStorageSync('userType')
            if (data.type == 'user_del' && userType){//如果用户被删除
              wx.removeStorageSync('userType')
              if (!that.pageLoading) {
                that.pageLoading = !0;
                wx.navigateTo({
                  url: '/jujiwuliu/pages/index/index'
                })
              }
            }
          }
      })

    },
    onShow: function (res) {
      let option = JSON.stringify(res);
      console.log('app.js option-----' + option)
      console.log('app.js>>res.scene--------------------' + res.scene);
      var resultScene = this.sceneInfo(res.scene);
      // wx.setStorageSync('at_show_firing_page', 0)//第一次打开或者从后台进入前台 则显示
      console.log(res.scene)
      if (res.scene){//通过场景进入才展示启动页面(防止用户点击个人中心也触发显示启动页面的bug)
        wx.setStorageSync('at_show_firing_page', 0)
      }
    },
  //场景值判断 目前没什么大的用处(预留方法)
  sceneInfo: function (s) {
    var scene = [];
    switch (s) {
      case '1001':
        scene.push(s, "发现栏小程序主入口");
        break;
      case '1005':
        scene.push(s, "顶部搜索框的搜索结果页");
        break;
      case '1006':
        scene.push(s, "发现栏小程序主入口搜索框的搜索结果页");
        break;
      case '1007':
        scene.push(s, "单人聊天会话中的小程序消息卡片");
        break;
      case '1008':
        scene.push(s, "群聊会话中的小程序消息卡片");
        break;
      case '1011':
        scene.push(s, "扫描二维码");
        break;
      case '1012':
        scene.push(s, "长按图片识别二维码");
        break;
      case '1014':
        scene.push(s, "手机相册选取二维码");
        break;
      case '1017':
        scene.push(s, "前往体验版的入口页");
        break;
      case '1019':
        scene.push(s, "微信钱包");
        break;
      case '1020':
        scene.push(s, "公众号profile页相关小程序列表");
        break;
      case '1022':
        console.log(111)
        scene.push(s, "聊天顶部置顶小程序入口");
        break;
      case '1023':
        scene.push(s, "安卓系统桌面图标");
        break;
      case '1024':
        scene.push(s, "小程序profile页");
        break;
      case '1025':
        scene.push(s, "扫描一维码");
        break;
      case '1026':
        scene.push(s, "附近小程序列表");
        break;
      case '1027':
        scene.push(s, "顶部搜索框搜索结果页“使用过的小程序”列表");
        break;
      case '1028':
        scene.push(s, "我的卡包");
        break;
      case '1029':
        scene.push(s, "卡券详情页");
        break;
      case '1031':
        scene.push(s, "长按图片识别一维码");
        break;
      case '1032':
        scene.push(s, "手机相册选取一维码");
        break;
      case '1034':
        scene.push(s, "微信支付完成页");
        break;
      case '1035':
        scene.push(s, "公众号自定义菜单");
        break;
      case '1036':
        scene.push(s, "App分享消息卡片");
        break;
      case '1037':
        scene.push(s, "小程序打开小程序");
        break;
      case '1038':
        scene.push(s, "从另一个小程序返回");
        break;
      case '1039':
        scene.push(s, "摇电视");
        break;
      case '1042':
        scene.push(s, "添加好友搜索框的搜索结果页");
        break;
      case '1044':
        scene.push(s, "带shareTicket的小程序消息卡片");
        break;
      case '1047':
        scene.push(s, "扫描小程序码");
        break;
      case '1048':
        scene.push(s, "长按图片识别小程序码");
        break;
      case '1049':
        scene.push(s, "手机相册选取小程序码");
        break;
      case '1052':
        scene.push(s, "卡券的适用门店列表");
        break;
      case '1053':
        scene.push(s, "搜一搜的结果页");
        break;
      case '1054':
        scene.push(s, "顶部搜索框小程序快捷入口");
        break;
      case '1056':
        scene.push(s, "音乐播放器菜单");
        break;
      case '1058':
        scene.push(s, "公众号文章");
        break;
      case '1059':
        scene.push(s, "体验版小程序绑定邀请页");
        break;
      case '1064':
        scene.push(s, "微信连Wifi状态栏");
        break;
      case '1067':
        scene.push(s, "公众号文章广告");
        break;
      case '1068':
        scene.push(s, "附近小程序列表广告");
        break;
      case '1072':
        scene.push(s, "二维码收款页面");
        break;
      case '1073':
        scene.push(s, "客服消息列表下发的小程序消息卡片");
        break;
      case '1074':
        scene.push(s, "公众号会话下发的小程序消息卡片");
        break;
      case '1089':
        scene.push(s, "微信聊天主界面下拉");
        break;
      case '1090':
        scene.push(s, "长按小程序右上角菜单唤出最近使用历史");
        break;
      case '1092':
        scene.push(s, "城市服务入口");
        break;
      default:
        scene.push(s, "未知入口");
        break;
    }
    return scene;
  },
    onHide: function () {

      console.log(11212)
    },
    onError: function (msg) {
        // console.log(msg)
    },
    //导航菜单，巨吉物流将会自己实现一个导航菜单，结构与小程序导航菜单相同
    //用户信息，sessionid是用户是否登录的凭证
    userInfo: {
        sessionid: null,
    },

    memberInfo:{},
    location:{},
    "issuerTabBar": {
        "color": "#999",
        //"selectedColor": "#f86b4f",
      "selectedColor": "#a7884f",
        "borderStyle": "#eee",
        "backgroundColor": "#fff",
        "list": [
            {
            "pagePath":  "/jujiwuliu/pages/issuer/index/index" ,
            "iconPath":  "/jujiwuliu/resource/icon/send.png" ,
            "selectedIconPath": "/jujiwuliu/resource/icon/sendselect.png" ,
            "text":"发布" 
            },
            {
              "pagePath": "/jujiwuliu/pages/index/index",
              "iconPath": "/jujiwuliu/resource/icon/user.png",
              "selectedIconPath": "/jujiwuliu/resource/icon/userselect2.png" ,
              "text": "我的"
            }
        ]
    },
  "workerTabBar": {
    "color": "#999",
    //"selectedColor": "#f86b4f",
    "selectedColor": "#a7884f",
    "borderStyle": "#eee",
    "backgroundColor": "#fff",
    "list": [
      {
        "pagePath":  "/jujiwuliu/pages/worker/index/index",
        "iconPath":"/jujiwuliu/resource/icon/take.png",
        "selectedIconPath":  "/jujiwuliu/resource/icon/takeselect.png",
        "text":  "接单"
      },
      {
        "pagePath": "/jujiwuliu/pages/index/index",
        "iconPath": "/jujiwuliu/resource/icon/user.png",
        "selectedIconPath": "/jujiwuliu/resource/icon/userselect.png",
        "text": "我的"
      }
    ]
  },
    siteInfo: siteInfo,
    globalData: {
      promise: null,
    }   
});