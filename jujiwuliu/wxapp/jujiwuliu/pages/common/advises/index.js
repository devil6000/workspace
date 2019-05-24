// jujiwuliu/pages/common/advises/index.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    model: ['投诉','建议'],
    modelIndex: -1
  },

  /**
   * 自定义方法
   */

  //获取投诉建议类型值
  modelChange: function(e){
    this.setData({
      modelIndex: e.detail.value
    })
  },

  getMobile: function(e){
    this.setData({
      mobile: e.detail.value
    })
  },

  //获取投诉建议主题
  getTitle: function(e){
    this.setData({
      title: e.detail.value
    })
  },

  //获取投诉建议内容
  getContent: function(e){
    this.setData({
      content: e.detail.value
    })
  },

  //选择图片
  addImage: function(e){
    var that = this;
    wx.chooseImage({
      success: function(res) {
        var tempFilepaths = res.tempFilePaths;
        wx.showToast({
          title: '正在上传 。。。',
           icon: 'loading',
           mask: true,
           duration: 1000
        })
        tempFilepaths.forEach(function(file_path,index){
          wx.uploadFile({
            url: app.util.getUrl('entry/wxapp/imgupload'),
            filePath: file_path,
            name: 'file',
            formData: {
              file: file_path
            },
            header: {
              "Content-Type": "multipart/form-data"
            },
            success: function(res){
              var info = JSON.parse(res.data);
              if(info.errno == 0){
                info = info.data;
                var info_images = that.data.images ? that.data.images : [];
                info_images.push(info.file_name);
                that.setData({
                  images: info_images
                })
              }else{
                var message = info.message;
                wx.showModal({
                  title: '获取失败',
                  content: message,
                  showCancel: false
                })
                return false;
              }
            }
          })
        })
      },
    })
  },

  //提交
  submit: function(e){
    var that = this;
    var model = that.data.modelIndex;
    if(model < 0){
      wx.showToast({
        title: '请选择提交类型',
        mask: false,
        duration: 1000
      })
      return false;
    }
    var mobile = that.data.mobile;
    var exp = /^((1[1-9]{2})+\d{8})$/;
    if(!exp.test(mobile)){
      wx.showToast({
        title: '联系方式格式不正确',
        mask: false,
        duration: 1000
      })
      return false;
    }
    var title = that.data.title;
    if(title == undefined || title == ''){
      wx.showToast({
        title: '请输入投诉建议主题',
        mask: false,
        duration: 1000
      })
      return false;
    }

    app.util.request({
      url: 'entry/wxapp/saveAdvises',
      data: {
        formid: e.detail.formId,
        model: model,
        title: title,
        mobile: mobile,
        content: that.data.content,
        images: that.data.images
      },
      method: 'POST',
      success: function(res){
        wx.navigateTo({
          url: '/jujiwuliu/pages/common/advises/list',
        })
      },
      fail: function(res){
        wx.showModal({
          title: '温馨提示',
          content: res.message,
          showCancel: false
        })
      }
    })
  },

  removeImage: function(e){
    var index = e.currentTarget.dataset.index;
    var images = this.data.images;
    images.splice(index,1);
    this.setData({
      images: images
    })
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