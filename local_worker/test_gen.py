import worker
import time

print("🧪 Starting Image Generation Test (Local ComfyUI)...")
prompt = "cyberpunk city street at night, neon lights, rain, detailed, anime style"

# Directly call gen function
print(f"Subject: {prompt}")
results = worker.generate_image_comfyui(prompt)

if results:
    filename, content = results[0]
    print(f"✅ Success! Image Generated.")
    
    # Save locally to view
    with open("test_output.png", "wb") as f:
        f.write(content)
    print(f"Saved to: {filename} (and test_output.png)")
else:
    print("❌ Failed. Check ComfyUI console for errors.")
