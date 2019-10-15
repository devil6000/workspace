// jujiwuliu/pages/common/user/balance/recharge/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    money:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },
  //获取input输入的值

  money_check: function (e) {
      var that = this;
      that.setData({
        money: e.detail.value,
      })

  },
  //充值方法
  recharge:function(){
    var that = this;
    if(that.data.is_recharge==1){
      return;
    }
    //获取金额
    if(that.data.money<=0){
      wx.showModal({
        title: '温馨提示',
        content: '请输入正确的金额！',
        showCancel:false
      })
      return ;
    }
    //创建充值订单
    app.util.request({
      url: 'entry/wxapp/recharge',
      data: {
        id: that.data.order_id,
        type:1,
        money:that.data.money
      },
      method: "POST",
      success: function (res) {//后台直接返回支付参数 节省请求 增加用户体验
        //缓存id
        if (res.data && res.data.data && !res.data.errno) {
          console.log(res);
          that.setData({
            order_id: res.data.data.id
          })

          //发起充值
          wx.requestPayment({
            'timeStamp': res.data.data.pay_data.timeStamp,
            'nonceStr': res.data.data.pay_data.nonceStr,
            'package': res.data.data.pay_data.package,
            'signType': 'MD5',
            'paySign': res.data.data.pay_data.paySign,
            'success': function (res) {
              //执行支付成功提示
              wx.showModal({
                title: '温馨提示',
                content: '充值成功！',
                showCancel:false,
                success:function(){
                  wx.navigateBack({ changed: true });//返回上一页
                }
              })
            },
            'fail': function (res) {
              // backApp()
            }
          })
        }
      },
      fail: function (res) {
        console.log(res)
        var message = res.data.message
        wx.showModal({
          title: '请求失败',
          content: message,
          showCancel: false
        });
        return false;
      }
    })



  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})