@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.cuisines_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a
                                href="{!! route('cuisines') !!}">{{trans('lang.cuisines_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.cuisines_edit')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#cuisines_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">{{trans('lang.cuisines_information')}}</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab"
                                   data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="cuisines_information">
                                    <fieldset>
                                        <legend>{{trans('lang.cuisines_edit')}}</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.cuisines_name')}}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control cat-name">
                                                <div class="form-text text-muted">{{ trans("lang.cuisines_name_help") }} </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">{{trans('lang.cuisines_description')}}</label>
                                            <div class="col-7">
                                <textarea rows="7" class="cuisines_description form-control"
                                          id="cuisines_description"></textarea>
                                                <div class="form-text text-muted">{{ trans("lang.cuisines_description_help") }}</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.cuisines_image')}}</label>
                                            <div class="col-7">
                                                <input type="file" id="cuisines_image">
                                                <div class="placeholder_img_thumb cat_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">{{ trans("lang.cuisines_image_help") }}</div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">{{trans('lang.item_publish')}}</label>
                                       </div>
                                        <div class="form-check row width-100" id="show_in_home">
                                            <input type="checkbox" id="show_in_homepage">
                                            <label class="col-3 control-label" for="show_in_homepage">{{trans('lang.show_in_home')}}</label>
                                            <div class="form-text text-muted w-50">{{trans('lang.show_in_home_desc')}}<span id="forsection"></span></div>
                                        </div>            
                                    </fieldset>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary edit-setting-btn"><i
                                    class="fa fa-save"></i> {{trans('lang.save')}}</button>
                        <a href="{!! route('cuisines') !!}" class="btn btn-default"><i
                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
var id = "<?php echo $id;?>";
if (!id) {
    $(document).ready(function () {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>Error: Cuisine ID is missing. Unable to load data.</p>");
        jQuery("#data-table_processing").hide();
    });
    throw new Error("Cuisine ID is missing");
}
var database = firebase.firestore();
var ref = database.collection('vendor_cuisines').doc(id);
var photo = "";
var fileName="";
var catImageFile="";
var placeholderImage = '';
var placeholder = database.collection('settings').doc('placeHolderImage');
var ref_review_attributes = database.collection('review_attributes');
var cuisines = '';
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();
    
    // Random review generation functions
    function generateRandomReviewCount() {
        // Generate random number between 70 and 130
        return Math.floor(Math.random() * (130 - 70 + 1)) + 70;
    }
    
    function generateRandomReviewSum() {
        // Generate random number between 4.8 and 5.0 with 1 decimal place
        return (Math.random() * (5.0 - 4.8) + 4.8).toFixed(1);
    }
    
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
$(document).ready(function () {
    jQuery("#data-table_processing").show();
    ref.get().then(async function (doc) {
        if (!doc.exists) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>Error: Cuisine not found for the given ID.</p>");
            jQuery("#data-table_processing").hide();
            return;
        }
        cuisines = doc.data();
        $(".cat-name").val(cuisines.title);
        $(".cuisines_description").val(cuisines.description);
        if (cuisines.photo != '' && cuisines.photo != null) {
              photo = cuisines.photo;
              catImageFile=cuisines.photo;
              $(".cat_image").append('<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:50px" src="' + photo + '" alt="image">');
        } else {
            $(".cat_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
        }
        if (cuisines.publish) {
            $("#item_publish").prop('checked', true);
        }
        if (cuisines.show_in_homepage) {
            $("#show_in_homepage").prop('checked', true);
        }
        jQuery("#data-table_processing").hide();
    })
    ref_review_attributes.get().then(async function (snapshots) {
        var ra_html = '';
        var reviewAttributesArr = Array.isArray(cuisines.review_attributes) ? cuisines.review_attributes : [];
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            ra_html += '<div class="form-check width-100" >';
            var checked = $.inArray(data.id, reviewAttributesArr) !== -1 ? 'checked' : '';
            ra_html += '<input type="checkbox" id="review_attribute_' + data.id + '" value="' + data.id + '" ' + checked + '>';
            ra_html += '<label class="col-3 control-label" for="review_attribute_' + data.id + '">' + data.title + '</label>';
            ra_html += '</div>';
        })
        $('#review_attributes').html(ra_html);
    })
    $(".edit-setting-btn").click(async function () {
        var title = $(".cat-name").val();
        var description = $(".cuisines_description").val();
        var item_publish = $("#item_publish").is(":checked");
        var show_in_homepage = $("#show_in_homepage").is(":checked");
        var review_attributes = [];
        $('#review_attributes input').each(function () {
            if ($(this).is(':checked')) {
                review_attributes.push($(this).val());
            }
        });
        if (title == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_cat_title_error')}}</p>");
            window.scrollTo(0, 0);
        } else {
            var count_vendor_categories = 0;
            if (show_in_homepage) {
                await database.collection('vendor_cuisines').where('show_in_homepage', "==", true).where("id", "!=", id).get().then(async function (snapshots) {
                    count_vendor_categories = snapshots.docs.length;
                });
            }
            if (count_vendor_categories >= 5) {
                alert("Already 5 categories are active for show in homepage..");
                return false;
            } else {
            jQuery("#data-table_processing").show();
            storeImageData().then(IMG => {
            database.collection('vendor_cuisines').doc(id).update({
                'title': title,
                'description': description,
                'photo': IMG,
                'review_attributes': review_attributes,
                'publish': item_publish,
                'show_in_homepage': show_in_homepage,
                // Review fields - Generate random realistic values
                'reviewCount': generateRandomReviewCount().toString(), // Random review count (70-130)
                'reviewSum': generateRandomReviewSum().toString(), // Random review sum (4.8-5.0)
                            }).then(async function (result) {
                    console.log('✅ Cuisine updated successfully, now logging activity...');
                    
                    // Log the activity with error handling and await the Promise
                    try {
                        if (typeof logActivity === 'function') {
                            console.log('🔍 Calling logActivity for cuisine update...');
                            await logActivity('cuisines', 'updated', 'Updated cuisine: ' + title);
                            console.log('✅ Activity logging completed successfully');
                        } else {
                            console.error('❌ logActivity function is not available');
                        }
                    } catch (error) {
                        console.error('❌ Error calling logActivity:', error);
                    }
                    
                    jQuery("#data-table_processing").hide();
                    window.location.href = '{{ route("cuisines")}}';
                });
             }).catch(err => {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + err + "</p>");
                window.scrollTo(0, 0);
            });
        }
        }
    });
});

    // Use global logActivity function from activity_logs page
    // This function will be available when the page loads

function handleFileSelect(evt) {
    var f = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = (function (theFile) {
        return function (e) {
            var filePayload = e.target.result;
            var val = $('#cuisines_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#cuisines_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            var uploadTask = storageRef.child(filename).put(theFile);
            uploadTask.on('state_changed', function (snapshot) {
                var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
            }, function (error) {
            }, function () {
                uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                    jQuery("#uploding_image").text("Upload is completed");
                    photo = downloadURL;
                    $(".cat_image").empty();
                    $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                });
            });
        };
    })(f);
    reader.readAsDataURL(f);
}
async function storeImageData() {
            var newPhoto = '';
            try {
                if (catImageFile != "" && photo != catImageFile) {
                    var catOldImageUrlRef = await storage.refFromURL(catImageFile);
                    imageBucket = catOldImageUrlRef.bucket; 
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await catOldImageUrlRef.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');  
                    }
                } 
                if (photo != catImageFile) {
                    photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    photo = downloadURL;
                } else {
                    newPhoto = photo;
                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }  
//upload image with compression
$("#cuisines_image").resizeImg({
    callback: function(base64str) {
        var val = $('#cuisines_image').val().toLowerCase();
        var ext = val.split('.')[1];
        var docName = val.split('fakepath')[1];
        var filename = $('#cuisines_image').val().replace(/C:\\fakepath\\/i, '')
        var timestamp = Number(new Date());
        var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
        photo=base64str;
        fileName=filename;
        $(".cat_image").empty();
        $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
        $("#cuisines_image").val('');
    }
});
</script>
@endsection