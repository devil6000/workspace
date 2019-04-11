var app = getApp()
Page({
  data:{
    usertype: '',
    memberInfo: ''
  },
  //发布方
  user: function (e) { 
    wx.navigateTo({
      url: '/jujiwuliu/pages/issuer/bind/index'
    });
  },
  //搬运工
  worker: function (e) {
    wx.navigateTo({
      url: '/jujiwuliu/pages/worker/bind/index'
    });
  },
  onLoad:function(options){
    
  },
  onReady:function(){
    // 页面渲染完成
  
  },
  onShow:function(){
    // 页面显示
    var that = this
    this.pageLoading = !1;//防止多次跳转
    app.util.getUserInfo(function (userInfo) {
      //获取到用户信息后再执行下面的操作
      if (userInfo.uid) {
        app.memberInfo = userInfo;
        that.setData({
          memberInfo: userInfo,
        });
        console.log(app.memberInfo)
        app.util.request({
          url: 'entry/wxapp/getcenter',
          data: {},
          method: "POST",
          success: function (res) {
            console.log(res);
            var info = res.data.data.info;
            if (info.type == 1) {
              wx.setStorageSync('userType', 'worker')
              wx.navigateTo({
                url: '/jujiwuliu/pages/worker/index'
              });
            } else {
              wx.setStorageSync('userType', 'user')
              wx.navigateTo({
                url: '/jujiwuliu/pages/issuer/index'
              });
            }
          },
          fail: function (res) {
            console.log(res)
            return false;
          }
        })
      } else {
        console.log('notlogin')
        //跳转到登陆引导页面
        if (!that.pageLoading) {
          that.pageLoading = !0;
          wx.navigateTo({
            url: '/jujiwuliu/pages/auth/index'
          })
        }
      }
    });
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  }
})