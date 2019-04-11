// jujiwuliu/pages/issuer/receipt/apply/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
      firms:[],
      firm:[],
      show_editbtn:0,
  },
    sub_del: function (e){
        var that=this;
       var firm= that.data.firms[e.currentTarget.dataset.firmindex];
        app.util.request({
            url: 'entry/wxapp/DelFirm',
            data: { id: firm.id},
            success: function (res) {
                console.log(res);

                that.onLoad();
            }
        })
    },
    sub_edit:function(e){
        var that = this;
        console.log(e.currentTarget.dataset.firmindex)
        that.setData({
            firm: that.data.firms[e.currentTarget.dataset.firmindex],
            show_editbtn:1,
        })
        
    },
    reset_firm:function(e){
        var that = this;
        that.setData({
            firm:[],
            show_editbtn:0,
        })
    },
    submit_firm:function(e){
        var that = this;
        var data = e.detail.value;
                data.id = that.data.firm.id;
        app.util.request({
            url: 'entry/wxapp/PostFirm',
            data: data ,
            success: function (res) {
                console.log(res);

                that.onLoad();
            }
        })
    },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      var that=this;
      app.util.request({
          url: 'entry/wxapp/GetFirmList',
         success:function(res){
             console.log(res.data.data);
             that.setData({
                 firms: res.data.data
             })
         }
          })
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