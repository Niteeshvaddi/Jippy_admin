@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Media</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('media.index') }}">Media</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#media_information" aria-controls="description" role="tab" data-toggle="tab"
                               class="nav-link active">Media Information</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="error_top" style="display:none"></div>
                    <div class="row restaurant_payout_create" role="tabpanel">
                        <div class="restaurant_payout_create-inner tab-content">
                            <div role="tabpanel" class="tab-pane active" id="media_information">
                                <fieldset>
                                    <legend>Edit Media</legend>
                                    <form id="mediaEditForm">
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Name <span class="text-danger">*</span></label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="media_name" required>
                                                <div class="form-text text-muted">Enter the media name</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Slug</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="media_slug" disabled>
                                                <div class="form-text text-muted">Auto-generated from name</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Image</label>
                                            <div class="col-7">
                                                <input type="file" id="media_image" accept="image/*">
                                                <div class="form-text text-muted">Select a new image (optional, Max size: 5MB, Supported formats: JPG, PNG, GIF)</div>
                                                <div class="media_image_preview mt-2"></div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Image Path</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="media_image_path" disabled>
                                                <div class="form-text text-muted">Auto-generated after upload</div>
                                            </div>
                                        </div>
                                    </form>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-media-btn"><i class="fa fa-save"></i> Update</button>
                    <a href="{{ route('media.index') }}" class="btn btn-default"><i class="fa fa-undo"></i> Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function slugify(text) {
    return text.toString().toLowerCase().replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

var id = "{{ $id ?? '' }}";
var database = firebase.firestore();
var ref = database.collection('media').doc(id);
var storageRef = firebase.storage().ref('images');
var photo = "";
var imageName = "";
var imagePath = "";
var oldImagePath = "";
var oldImageName = "";
var isUploading = false;

$(document).ready(function () {
    // Load existing media data
    ref.get().then(function (doc) {
        if (!doc.exists) {
            $('.error_top').show().html('<p>Error: Media not found for the given ID.</p>');
            return;
        }
        var media = doc.data();
        $('#media_name').val(media.name);
        $('#media_slug').val(media.slug);
        $('#media_image_path').val(media.image_path);
        oldImagePath = media.image_path;
        oldImageName = media.image_name || '';
        $('.media_image_preview').html(media.image_path ? '<img class="rounded" style="width:70px; height:70px; object-fit: cover;" src="' + media.image_path + '" alt="current image">' : '');
    });

    // Generate slug when name changes
    $('#media_name').on('input', function () {
        var name = $(this).val().trim();
        if (name) {
            var slug = 'media-' + slugify(name);
            imageName = 'media_' + slug + '_' + Date.now();
            $('#media_slug').val(slug);
        } else {
            $('#media_slug').val('');
            imageName = '';
        }
    });

    // Handle new image file selection
    $('#media_image').change(function (evt) {
        var f = evt.target.files[0];
        if (!f) {
            photo = "";
            return;
        }
        
        // Validate file type
        if (!f.type.startsWith('image/')) {
            $('.error_top').show().html('<p>Please select a valid image file.</p>');
            window.scrollTo(0, 0);
            $(this).val('');
            return;
        }
        
        // Validate file size (max 5MB)
        if (f.size > 5 * 1024 * 1024) {
            $('.error_top').show().html('<p>Image size should not exceed 5MB.</p>');
            window.scrollTo(0, 0);
            $(this).val('');
            return;
        }
        
        var reader = new FileReader();
        reader.onload = function (e) {
            photo = e.target.result;
            $('.media_image_preview').html('<img class="rounded" style="width:70px; height:70px; object-fit: cover;" src="' + photo + '" alt="new image preview">');
        };
        reader.readAsDataURL(f);
    });

    // Update media
    $('.save-media-btn').click(async function () {
        if (isUploading) return;
        
        var name = $('#media_name').val().trim();
        var slug = $('#media_slug').val();
        
        // Validation
        if (!name) {
            $('.error_top').show().html('<p>Please enter a media name.</p>');
            window.scrollTo(0, 0);
            return;
        }
        
        $('.error_top').hide();
        isUploading = true;
        $('.save-media-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        
        try {
            let newImagePath = oldImagePath;
            let newImageName = oldImageName;
            
            // Upload new image if selected
            if (photo && imageName) {
                var uploadTask = storageRef.child(imageName).putString(
                    photo.replace(/^data:image\/[a-z]+;base64,/, ''), 
                    'base64', 
                    {contentType: 'image/jpeg'}
                );
                
                var snapshot = await uploadTask;
                newImagePath = await snapshot.ref.getDownloadURL();
                newImageName = imageName;
                $('#media_image_path').val(newImagePath);
                
                // Delete old image if it exists and is different
                if (oldImageName && oldImageName !== newImageName) {
                    try {
                        var oldImageRef = storageRef.child(oldImageName);
                        await oldImageRef.delete();
                    } catch (deleteError) {
                        console.warn('Could not delete old image:', deleteError);
                    }
                }
            }
            
            // Update Firestore document
            var updateData = {
                name: name,
                slug: slug,
                updated_at: new Date()
            };
            
            if (photo && imageName) {
                updateData.image_name = newImageName;
                updateData.image_path = newImagePath;
            }
            
            await ref.update(updateData);
            
            // Log activity
            await logActivity('media', 'updated', 'Updated media: ' + name);
            
            // Success message
            $('.error_top').removeClass('alert-danger').addClass('alert-success').show().html('<p>Media updated successfully!</p>');
            
            // Redirect after short delay
            setTimeout(function() {
                window.location.href = '{{ route('media.index') }}';
            }, 1500);
            
        } catch (error) {
            console.error('Error updating media:', error);
            $('.error_top').show().html('<p>Error updating media: ' + error.message + '</p>');
            window.scrollTo(0, 0);
        } finally {
            isUploading = false;
            $('.save-media-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Update');
        }
    });
});
</script>
@endsection