<?php

return [
    // Authentication
    'auth' => [
        'otp_sent' => 'OTP sent via email',
        'otp_resent' => 'OTP resent successfully',
        'otp_verified' => 'OTP verified successfully',
        'invalid_otp' => 'Invalid or expired OTP.',
        'account_not_found' => 'Account not found.',
        'logged_in' => 'Logged in successfully',
        'logged_out' => 'Logged out successfully',
        'password_changed' => 'Password changed successfully',
        'password_reset' => 'Password reset successfully',
        'invalid_credentials' => 'Email or password is incorrect',
        'business_owner_required' => 'Business owner access is required.',
        'email_exists' => 'The email has already been taken.',
        'registration_success' => 'Registration successful',
        'reset_verified' => 'OTP verified. You can now reset your password.',
        'if_account_exists' => 'If the account exists, an OTP has been sent.',
        'current_password_incorrect' => 'Current password is incorrect.',
    ],

    // Validation
    'validation' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'unique' => 'The :attribute has already been taken.',
        'min' => 'The :attribute must be at least :min characters.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'digits' => 'The :attribute must be :digits digits.',
        'in' => 'The selected :attribute is invalid.',
        'max' => 'The :attribute may not be greater than :max characters.',
        'exists' => 'The selected :attribute is invalid.',
    ],

    // Business
    'business' => [
        'created' => 'Business created successfully',
        'updated' => 'Business updated successfully',
        'deleted' => 'Business deleted successfully',
        'not_found' => 'Business not found',
        'unauthorized' => 'You are not authorized to perform this action',
        'expired' => 'Business has expired',
        'approval_required' => 'Business approval is required',
        'status_updated' => 'Business status updated successfully',
    ],

    // Listings
    'listings' => [
        'retrieved' => 'Listings retrieved successfully',
        'not_found' => 'No listings found',
    ],

    // Reviews
    'reviews' => [
        'created' => 'Review created successfully',
        'updated' => 'Review updated successfully',
        'deleted' => 'Review deleted successfully',
        'not_found' => 'Review not found',
        'cannot_review_own_business' => 'You cannot review your own business',
        'already_reviewed' => 'You have already reviewed this business',
    ],

    // Media
    'media' => [
        'uploaded' => 'Media uploaded successfully',
        'deleted' => 'Media deleted successfully',
        'not_found' => 'Media not found',
        'invalid_type' => 'Invalid media type',
        'too_large' => 'File size exceeds the maximum limit',
    ],

    // Chat
    'chat' => [
        'message_sent' => 'Message sent successfully',
        'conversation_created' => 'Conversation created successfully',
        'not_found' => 'Conversation not found',
        'not_participant' => 'You are not a participant in this conversation',
    ],

    // Social
    'social' => [
        'connected' => 'Social account connected successfully',
        'disconnected' => 'Social account disconnected successfully',
    ],

    // General
    'general' => [
        'success' => 'Operation completed successfully',
        'error' => 'An error occurred. Please try again.',
        'not_found' => 'Resource not found',
        'unauthorized' => 'Unauthorized access',
        'forbidden' => 'Access forbidden',
        'validation_error' => 'Validation error',
        'server_error' => 'Internal server error',
        'no_data' => 'No data available',
    ],

    // Pagination
    'pagination' => [
        'previous' => '&laquo; Previous',
        'next' => 'Next &raquo;',
    ],
];
