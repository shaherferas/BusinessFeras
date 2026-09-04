<?php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel; use Illuminate\Contracts\Broadcasting\ShouldBroadcast; use Illuminate\Foundation\Events\Dispatchable; use Illuminate\Queue\SerializesModels;
class MessageDelivered implements ShouldBroadcast { use Dispatchable,SerializesModels; public function __construct(public int $conversationId,public int $messageId,public int $userId){} public function broadcastOn(): array{return [new PrivateChannel('conversation.'.$this->conversationId)];} public function broadcastAs(): string{return 'message.delivered';} public function broadcastWith(): array{return ['message_id'=>$this->messageId,'user_id'=>$this->userId];} }
