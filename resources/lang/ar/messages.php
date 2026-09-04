<?php

return [
    // Authentication
    'auth' => [
        'otp_sent' => 'تم إرسال رمز التحقق عبر البريد الإلكتروني',
        'otp_resent' => 'تم إعادة إرسال رمز التحقق بنجاح',
        'otp_verified' => 'تم التحقق من الرمز بنجاح',
        'invalid_otp' => 'رمز التحقق غير صالح أو منتهي الصلاحية.',
        'account_not_found' => 'الحساب غير موجود.',
        'logged_in' => 'تم تسجيل الدخول بنجاح',
        'logged_out' => 'تم تسجيل الخروج بنجاح',
        'password_changed' => 'تم تغيير كلمة المرور بنجاح',
        'password_reset' => 'تم إعادة تعيين كلمة المرور بنجاح',
        'invalid_credentials' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
        'business_owner_required' => 'مطلوب صلاحية صاحب العمل.',
        'email_exists' => 'البريد الإلكتروني مستخدم بالفعل.',
        'registration_success' => 'تم التسجيل بنجاح',
        'reset_verified' => 'تم التحقق من الرمز. يمكنك الآن إعادة تعيين كلمة المرور.',
        'if_account_exists' => 'إذا كان الحساب موجوداً، سيتم إرسال رمز التحقق.',
        'current_password_incorrect' => 'كلمة المرور الحالية غير صحيحة.',
    ],

    // Validation
    'validation' => [
        'required' => 'حقل :attribute مطلوب.',
        'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالح.',
        'unique' => ':attribute مستخدم بالفعل.',
        'min' => 'يجب أن يكون :attribute على الأقل :min حرفاً.',
        'confirmed' => 'تأكيد :attribute غير متطابق.',
        'digits' => 'يجب أن يكون :attribute :digits أرقام.',
        'in' => ':attribute المحدد غير صالح.',
        'max' => 'لا يمكن أن يتجاوز :attribute :max حرفاً.',
        'exists' => ':attribute المحدد غير صالح.',
    ],

    // Business
    'business' => [
        'created' => 'تم إنشاء العمل بنجاح',
        'updated' => 'تم تحديث العمل بنجاح',
        'deleted' => 'تم حذف العمل بنجاح',
        'not_found' => 'العمل غير موجود',
        'unauthorized' => 'غير مصرح لك بتنفيذ هذا الإجراء',
        'expired' => 'انتهت صلاحية العمل',
        'approval_required' => 'مطلوب موافقة على العمل',
        'status_updated' => 'تم تحديث حالة العمل بنجاح',
    ],

    // Listings
    'listings' => [
        'retrieved' => 'تم استرجاع القوائم بنجاح',
        'not_found' => 'لم يتم العثور على قوائم',
    ],

    // Reviews
    'reviews' => [
        'created' => 'تم إنشاء المراجعة بنجاح',
        'updated' => 'تم تحديث المراجعة بنجاح',
        'deleted' => 'تم حذف المراجعة بنجاح',
        'not_found' => 'المراجعة غير موجودة',
        'cannot_review_own_business' => 'لا يمكنك مراجعة عملك الخاص',
        'already_reviewed' => 'لقد قمت بمراجعة هذا العمل بالفعل',
    ],

    // Media
    'media' => [
        'uploaded' => 'تم رفع الوسائط بنجاح',
        'deleted' => 'تم حذف الوسائط بنجاح',
        'not_found' => 'الوسائط غير موجودة',
        'invalid_type' => 'نوع الوسائط غير صالح',
        'too_large' => 'حجم الملف يتجاوز الحد الأقصى',
    ],

    // Chat
    'chat' => [
        'message_sent' => 'تم إرسال الرسالة بنجاح',
        'conversation_created' => 'تم إنشاء المحادثة بنجاح',
        'not_found' => 'المحادثة غير موجودة',
        'not_participant' => 'أنت لست مشاركاً في هذه المحادثة',
    ],

    // Social
    'social' => [
        'connected' => 'تم ربط الحساب الاجتماعي بنجاح',
        'disconnected' => 'تم فصل الحساب الاجتماعي بنجاح',
    ],

    // General
    'general' => [
        'success' => 'تمت العملية بنجاح',
        'error' => 'حدث خطأ. يرجى المحاولة مرة أخرى.',
        'not_found' => 'المورد غير موجود',
        'unauthorized' => 'وصول غير مصرح',
        'forbidden' => 'الوصول محظور',
        'validation_error' => 'خطأ في التحقق',
        'server_error' => 'خطأ داخلي في الخادم',
        'no_data' => 'لا توجد بيانات متاحة',
    ],

    // Pagination
    'pagination' => [
        'previous' => '&laquo; السابق',
        'next' => 'التالي &raquo;',
    ],
];
