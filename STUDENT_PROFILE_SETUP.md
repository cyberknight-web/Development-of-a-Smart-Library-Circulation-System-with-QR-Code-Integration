# Student Profile Feature - Setup Guide

## Overview
This feature adds a complete student profile management system with:
- Profile picture upload (JPG, PNG, GIF)
- Edit full name, course, and section
- View student ID and email (read-only)
- Secure image storage

## Files Created

### 1. **student/profile.php** 
   - Displays student profile information
   - Shows profile picture
   - Provides form to edit name, course, section
   - Allows picture upload or deletion

### 2. **student/profile_action.php**
   - Handles profile picture uploads (5MB max, image files only)
   - Updates student profile information
   - Deletes old pictures when new ones are uploaded
   - Manages file operations securely

### 3. Updated Files
   - **schema.sql**: Added `profile_picture` and `updated_at` columns to students table
   - **includes/student_layout.php**: Added "My Profile" link to navigation menu

### 4. Security Files
   - **uploads/.htaccess**: Prevents PHP execution in uploads folder
   - **uploads/profiles/.htaccess**: Blocks scripts, allows only images

## Database Setup

Run this SQL to update existing database (or reimport schema.sql):

```sql
ALTER TABLE students 
ADD COLUMN profile_picture VARCHAR(255) NULL,
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
```

## Folder Structure

```
smartlibrary/
├── student/
│   ├── profile.php              (NEW)
│   ├── profile_action.php       (NEW)
│   └── ... other student files
├── uploads/
│   ├── .htaccess               (NEW - Security)
│   └── profiles/
│       ├── .htaccess           (NEW - Security)
│       └── index.php           (NEW - Empty directory marker)
└── schema.sql                   (UPDATED)
```

## Features

### 1. Profile Picture Management
- **Upload**: Click "Choose Image" button, select JPG/PNG/GIF (max 5MB)
- **Display**: Shows circular profile picture (150x150px)
- **Remove**: Delete current picture with "Remove Picture" button
- **Auto-delete**: Old pictures are deleted when new ones are uploaded

### 2. Profile Information Editing
- **Name**: Edit full name (max 100 characters)
- **Course**: Edit course name (max 100 characters)
- **Section**: Edit section (max 50 characters)
- **Read-only**: Student ID and Email cannot be changed
- **Auto-update**: Session data is updated immediately after save

### 3. Security
- File type validation (MIME type check)
- File size limits (5MB max)
- Unique filenames prevent collisions
- PHP execution prevented in upload folders
- Directory listing disabled
- XSS protection with htmlspecialchars()
- SQL injection prevention with prepared statements

### 4. Error Handling
- Invalid file type error
- File size exceeded error
- Upload failed error
- Profile update errors with user-friendly messages
- All errors logged to error_log

## Usage

### For Students

1. **Navigate to Profile**
   - Click username in top-right dropdown
   - Select "My Profile"

2. **Upload Picture**
   - Click "Choose Image" button
   - Select JPG, PNG, or GIF file (max 5MB)
   - Click "Upload Picture"

3. **Edit Profile**
   - Modify Name, Course, or Section fields
   - Click "Save Changes"
   - Success message appears

4. **Remove Picture**
   - Click "Remove Picture" button (if picture exists)
   - Picture is deleted from server

## Technical Details

### File Upload Process
1. Validate file size (max 5MB)
2. Validate MIME type (image/jpeg, image/png, image/gif)
3. Create uploads/profiles directory if not exists
4. Delete old picture if exists
5. Generate unique filename: `student_{id}_{timestamp}.{ext}`
6. Move uploaded file to secure directory
7. Update database with new filename
8. Return success/error status

### Filename Format
- **Example**: `student_5_1703251234.jpg`
- Prevents conflicts between multiple students
- Uses timestamp for uniqueness
- Original extension preserved

### Database Updates
```
Profile Picture: VARCHAR(255) - stores filename only, not full path
Updated At: TIMESTAMP - automatically tracks last modification
```

### Image Storage Path
```
/smartlibrary/uploads/profiles/
└── student_1_1703251234.jpg
└── student_2_1703251240.png
└── student_5_1703251234.gif
```

## Image URL Format
```
BASE_URL/uploads/profiles/student_{id}_{timestamp}.{ext}

Example:
http://localhost/smartlibrary/uploads/profiles/student_5_1703251234.jpg
```

## API Endpoints

### GET /student/profile.php
- Displays student profile page
- Query params:
  - `status=updated` - Success message
  - `status=error` - Generic error
  - `status=invalid_file` - Wrong file type
  - `status=size_error` - File too large
  - `status=file_error` - Upload failed

### POST /student/profile_action.php
- **Action 1: Upload Picture**
  - Files: `profile_picture` (multipart/form-data)
  - Saves image and updates database

- **Action 2: Update Profile**
  - Fields: `name`, `course`, `section`
  - Updates student table and session

- **Action 3: Delete Picture**
  - Action: `delete_picture`
  - Removes image file and database reference

## Testing Checklist

- [ ] Database schema updated successfully
- [ ] Profile page loads without errors
- [ ] Picture upload with valid JPG/PNG/GIF works
- [ ] Profile information editing works
- [ ] Session updates after profile change
- [ ] Picture deletion works
- [ ] Invalid file type rejected
- [ ] Large files (>5MB) rejected
- [ ] Old pictures deleted when new one uploaded
- [ ] Navigation shows "My Profile" link
- [ ] Profile picture displays in circular format
- [ ] Read-only fields cannot be edited (Student ID, Email)

## Troubleshooting

### Profile picture not uploading
**Solution**: 
- Check file size (max 5MB)
- Use JPG, PNG, or GIF format
- Ensure uploads/profiles directory has write permissions
- Check PHP error logs for details

### Picture not displaying
**Solution**:
- Verify uploads/profiles directory exists
- Check file permissions (should be readable)
- Verify filename in database

### Changes not saving
**Solution**:
- Check for validation errors on form
- Verify name/course/section not empty
- Check field length limits
- Review PHP error logs

### Permission denied errors
**Solution**:
```bash
# From command line in smartlibrary folder:
mkdir -p uploads/profiles
chmod 755 uploads
chmod 755 uploads/profiles
```

## Security Notes

✓ CSRF tokens recommended - Add to profile_action.php if needed
✓ File execution blocked in uploads folder via .htaccess
✓ All user input sanitized with htmlspecialchars()
✓ Prepared statements prevent SQL injection
✓ File type validation using finfo
✓ File size limits enforced
✓ Old files cleaned up automatically
✓ Directory listing disabled

## Future Enhancements

1. **Image Cropping**: Allow students to crop/resize pictures
2. **Image Compression**: Optimize uploaded images
3. **CSRF Tokens**: Add CSRF protection to all forms
4. **Audit Trail**: Track profile changes with timestamps
5. **Profile Visibility**: Allow students to manage who sees their info
6. **Gravatar Fallback**: Use Gravatar if no picture uploaded
7. **Batch Operations**: Allow admins to update multiple profiles
8. **Export Data**: Download student profile as PDF

## Support

For issues or questions:
1. Check error logs: `error_log`
2. Verify file permissions on uploads folder
3. Test with different image formats
4. Clear browser cache and reload
